<?php

namespace Recompany\Sourcemanager;

use Symfony\Component\Yaml\Yaml;
use Config;

/**
 * Description of Sourcemanager
 *
 * @author ruslan007
 */
class Sourcemanager {

    protected static $_permissions = NULL;                 // все доступные дейсвия
    protected $_permissions_resources = NULL;         // файлы с наборами разрешений

    protected $_URLs = array();
    protected $_IDs = array();

    protected $_kitPermission = NULL;                 // название набора разрешений ("ADMIN", "GO", "APPROVED")
    protected $_id_user = NULL;

    protected $_received_id = NULL;
    protected $_type_received_id = NULL;

    private static $_me = NULL;
    protected static $_container = NULL;

    private function __construct(){
        self::$_permissions = Yaml::parse(file_get_contents(Config::get('sourcemanager::config.permissions_path')));

        if(is_file(Config::get('sourcemanager::config.callback_path')))
            require_once Config::get('sourcemanager::config.callback_path');
    }

    public static function getInstance(){
        if(!self::$_me) self::$_me = new Sourcemanager();

        return self::$_me;
    }

    public function getPermissions() {
        return self::$_permissions;
    }

    public static function __callStatic($name, $arguments) {
        $n = 'Recompany\Sourcemanager\Workers\\'.substr($name, 6);
        self::$_container[$n] = new $n;
    }
                                // Проверяем URI
    public function checkUri($uri, $method) {
                                // Отделяем id от URI
        $id = $this->_decomposeUri($uri);       //"project/25" -> $uri = "project", $id = 25;
                                // Тип передеанного id: project_id или order_id
        $type_id = $this->_getTypeReceivedId($uri);
                                // Проходим по каждому workerу {role, project status, order status}
        foreach(self::$_container as $worker){
                                // Передаем в worker uri и id
            $worker->_setReceivedId($id, $type_id);
                                // Проверяем URI по каждому workerу
            if(!$worker->checkUri($uri, $method)) { return false; }
        }
        return true;
    }
                                // Получаем idшники, которые нужно удалить
    public function getIds() {
        foreach(self::$_container as $worker){
            $this->_IDs = array_merge($this->_IDs, $worker->getIds()); // Сливаем массивы
        }
        return array_unique($this->_IDs, SORT_REGULAR); // Возвращаем только уникальные id
    }
                                // Отделяем id от URI
    public function _decomposeUri(&$uri) {
        $u = explode("/", $uri);
        $i = end($u);                                               // string id
        $id = (int) $i;                                             // int id
        if ((string) $id == $i AND $id != 0){                       // Если есть id - обрезаем uri
            $uri = substr($uri, 0, strlen($uri) - strlen($i) - 1);
        }
        return $id;
    }
                                // Достаем тип передеанного id
    public function _getTypeReceivedId($path_uri) {
        foreach (self::$_permissions as $right => $content){
            if(isset($content["url"]) AND isset($content["type_id"]) AND $path_uri == $content["url"]) {
                return $content["type_id"];
            }
        }
        return false;
    }
                                // Передаем в worker uri и id
    public function _setReceivedId($id, $type_id) {
        $this->_received_id = $id;
        $this->_type_received_id = $type_id;
    }
                                // Сверяем URL и метод запроса
    protected function _isAllow($uri, $method) {
        $res = false;
        foreach ($this->_URLs as $key => $url){
            if((is_array($url['method']) && in_array($method ,$url['method']))
                    || $url['method'] == $method){
                if($url['url'] == $uri) $res = true;
                else if($url['length'] && $url['url'] == substr($uri, 0, $url['length'])) $res = true;
            }
        }
        return $res;
    }

    protected function _calculateURLs() {
        $rights = $this->_permissions_resources[$this->_kitPermission];
        $urls = array();
        foreach (Sourcemanager::$_permissions as $right => $content)
            if(isset($content["url"]))
                $urls[$right] = [
                    "url" => $content["url"],
                    "method" => (isset($content["method"])) ? $content["method"] : "",
                    "length" => (isset($content["length"])) ? $content["length"] : false,
                    "callback" => (isset($content["callback"])) ? $content["callback"] : false
                ];

            if ("all allow" == $rights["permission"])
            $this->_URLs = $urls;
//echo $this->_kitPermission.PHP_EOL;
        if(empty($rights["pages"])) return;

        foreach ($rights["pages"] as $id => $value) {
            $this->_setURL($value, $id, $urls);
        }
//print_r(array_keys($this->_URLs));
//die();
        return;


    }

    protected function _setURL($type, $id, array $urls) {
//        echo "$type, $id,".PHP_EOL;
        // если функция пользователя сделав доп проверки по аргументам вернула false - т.е. не тот случай, то выходим, правило не применимо. это нужно для честичного блока по урл при определеных вх параметрах
        if(isset($urls[$id]) && $urls[$id]["callback"] && !call_user_func($urls[$id]["callback"], $urls[$id]))
            return;

        if ("deny" == $type AND isset($this->_URLs[$id])) unset($this->_URLs[$id]);
        if ("allow" == $type AND isset($urls[$id])){
            $this->_URLs[$id] = $urls[$id];

            $up = explode(".", $id);
            $s = $id;
            foreach ($up as $key => $idPart) {
                $s = substr($s, 0, strrpos($s, ".", -1));
                if(isset($urls[$s]))
                    $this->_URLs[$s] = $urls[$s];
            }
        }

        if(isset(Sourcemanager::$_permissions[$id]["items"]))
            foreach (Sourcemanager::$_permissions[$id]["items"] as $sId => $name)
                $this->_setURL($type, $sId, $urls);
    }


    protected function _calculateIDs(){
        $rights = $this->_permissions_resources[$this->_kitPermission];
        $ids = array();
        foreach (Sourcemanager::$_permissions as $right => $content) {
            if(isset($content["ids"]))
                $ids[$right] = $right;
        }

        if ("all deny" == $rights["permission"])
            $this->_IDs = $ids;

        if(!empty($rights["pages"])){
            foreach ($rights["pages"] as $id => $value) {
                $this->_setId($value, $id);
            }
        }
        $this->_createRealId();
//print_r($this->_IDs);
//die;

        return;
    }

    protected function _setId($type, $id) {
        if ("deny" == $type) $this->_IDs[$id] = $id;
        if ("allow" == $type){
            unset($this->_IDs[$id]);


            $up = explode(".", $id);
            $s = $id;
            foreach ($up as $key => $idPart) {
                $s = substr($s, 0, strrpos($s, ".", -1));
                unset($this->_IDs[$s]);
            }
        }

        if (isset(Sourcemanager::$_permissions[$id]["items"]))
            foreach (Sourcemanager::$_permissions[$id]["items"] as $sId => $name)
                    $this->_setId($type, $sId);
    }

    protected function _createRealId() {
        $ids = [];
                                    // Вытаскиваем id из массива permissions
        foreach (Sourcemanager::$_permissions as $right => $content) {
            if(isset($content["ids"]) AND isset($this->_IDs[$right])
                    AND $right == $this->_IDs[$right]){
                $ids[$right] = $content["ids"];
            }
        }
        $this->_IDs = $ids;
//        $removeIds = [];
//                                    // Формируем один цельный массив idшников
//        foreach ($ids as $right => $content) {
//            foreach ($content as $key => $id)
//                if(!in_array($id, $removeIds)) $removeIds[] = $id;
//        }
//        $this->_IDs = $removeIds;
//        var_dump($ids);
//        die();
    }
}
