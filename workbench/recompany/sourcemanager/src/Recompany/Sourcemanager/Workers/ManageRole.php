<?php
namespace Recompany\Sourcemanager\Workers;

use Symfony\Component\Yaml\Yaml;
use Config;
use Auth;
use Role;
use Item;
use Waybill;
use Recompany\Sourcemanager\Sourcemanager;

class ManageRole extends Sourcemanager implements SourcemanagerWorkerInterface {

    public function __construct(){
        $this->_permissions_resources = Yaml::parse(file_get_contents(Config::get('sourcemanager::config.rolies_path')));
        $this->_id_user  = Auth::user()->id;
        $this->_kitPermission = Auth::user()->role;

        $this->_calculateURLs();
        $this->_calculateIDs();
    }
                                // Проверяем URI
    public function checkUri($uri, $method) {
                                // Если без url без id - работа не с проектом - доступ только админу и LDC
        if(!$this->_received_id || !$this->_type_received_id || 'for_all' == $this->_type_received_id) { return $this->_checkWithoutId($uri, $method); }
                                // Если не админ и не ПСО - считаем url, доступные ему в проекте
                                // и id, которые нужно удалить
        if(!('ADMIN' == $this->_kitPermission || 'LDC' == $this->_kitPermission)) {
            $this->_calculateForRolesInProj();
        }
                                // Проверяем право доступа к этому uri
        return $this->_isAllow($uri, $method);
    }

    public function getIds() {
        return $this->_IDs;
    }

    public function _calculateForRolesInProj() {
        $roles = $this->_getRolesInProj($this->_received_id, $this->_type_received_id);
                            // Если роли нет - на выход
        if(!$roles) return false;

        $all_URLs = [];
        $all_IDs = [];
                            // Для каждой роли считаем URL и ID
        foreach($roles as $role){
//                print_r($role."<br>");
            $this->_kitPermission = $role;
            $this->_calculateURLs();
            $this->_calculateIDs();
//                var_dump($this->_URLs);
                            // Складываем доступные Url в один массив
            $all_URLs = array_merge($all_URLs, $this->_URLs);
                            // Находим ID - общие для всех ролей, остальные отсеиваются
            if(!$all_IDs)
                $all_IDs = $this->_IDs;
            else
                $all_IDs = array_intersect_key($all_IDs, $this->_IDs);
//                var_dump($all_IDs);
        }
                            // Оставляем только уникальные URL
        $this->_URLs = array_unique($all_URLs, SORT_REGULAR);
        $this->_IDs = $all_IDs;
//print_r(array_keys($this->_URLs));
//print_r(array_keys($this->_IDs));
//die();
    }
                            // Если URI без id - работа не с проектом
    public function _checkWithoutId($uri, $method) {
        if( ('ADMIN' == $this->_kitPermission || 'LDC' == $this->_kitPermission) &&
                $this->_isAllow($uri, $method)) { return true; }
        if('for_all' == $this->_type_received_id) { return true; }   // Блоки доступны всем
        return false;
    }
                            // Достаем роль usera в проекте по полученному id - проекта или заявки
    public function _getRolesInProj($id, $type_id) {
        if('proj_id' == $type_id) {
            return Role::getRolesByProjId($id, $this->_id_user);
        }
        if('order_id' == $type_id) {
            return Role::getRolesByOrderId($id, $this->_id_user);
        }
        if('item_id' == $type_id) {
            return Role::getRolesByOrderId(Item::getOrderId($id), $this->_id_user);
        }
        if('waybill_id' == $type_id) {
            return Role::getRolesByWaybillId($id, $this->_id_user);
        }
        return false;
    }
}
