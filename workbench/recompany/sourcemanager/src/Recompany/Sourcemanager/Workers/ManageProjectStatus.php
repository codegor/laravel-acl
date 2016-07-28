<?php
namespace Recompany\Sourcemanager\Workers;

use Symfony\Component\Yaml\Yaml;
use Config;
use Project;
use Order;
use Item;
use Waybill;
use Recompany\Sourcemanager\Sourcemanager;

class ManageProjectStatus extends Sourcemanager implements SourcemanagerWorkerInterface {
    public function __construct(){
        $this->_permissions_resources = Yaml::parse(file_get_contents(Config::get('sourcemanager::config.project_status_path')));
    }

    public function checkUri($uri, $method) {
                                // Если без url без id - работа не с проектом - не обрабатываем
        if(!$this->_received_id || !$this->_type_received_id || 'for_all' == $this->_type_received_id) { return true; }
                                    // Достаем статус проекта
        $project_status = $this->_getProjStatus($this->_received_id, $this->_type_received_id);
        if($project_status)
            $this->_kitPermission = $project_status; else return false;
                                    // Достаем тип проекта и соотв. правила
        $project_type = $this->_getProjType($this->_received_id, $this->_type_received_id);
        if('renew' == $project_type) {
            $this->_addRenewRules();
        }

        $this->_calculateURLs();
                                    // Проверяем доступ по статусу
        return $this->_isAllow($uri, $method);
    }

    public function getIds(){
                                    // Если работаем не с проектом - пустой массив
        if(!$this->_received_id || !$this->_type_received_id) return [];
        $this->_calculateIDs();
        return $this->_IDs;
    }
                                    // Если реконструкция - добавляем её правила
    public function _addRenewRules(){
        $this->_permissions_resources['NEW']['pages']['bb.project.projSettings.toApprove'] = "allow";
        $this->_permissions_resources['NEW']['pages']['bb.project.projSettings.toPreapprove'] = "deny";
        $this->_permissions_resources['FORMED']['pages']['bb.project.projSettings.backToChange'] = "allow";
        $this->_permissions_resources['FORMED']['pages']['bb.project.projSettings.backToApprove'] = "deny";
    }
                                    // Достаем статус проекта
    public function _getProjStatus($id, $type_id) {
        if('proj_id' == $type_id) {
            return Project::getStatus($id);
        }
        if('order_id' == $type_id) {
            return Project::getStatus(Order::getProjId($id));
        }
        if('item_id' == $type_id) {
            return Project::getStatus(Order::getProjId(Item::getOrderId($id)));
        }
        if('waybill_id' == $type_id) {
            return Waybill::getWaybillProjectStatus($id);
        }
        return false;
    }
                                    // Достаем тип проекта
    public function _getProjType($id, $type_id) {
        if('proj_id' == $type_id) {
            return Project::getType($id);
        }
        if('order_id' == $type_id) {
            return Project::getType(Order::getProjId($id));
        }
        if('item_id' == $type_id) {
            return Project::getType(Order::getProjId(Item::getOrderId($id)));
        }
        if('waybill_id' == $type_id) {
            return Waybill::getWaybillProjectType($id);
        }
        return false;
    }
}
