<?php
namespace Recompany\Sourcemanager\Workers;

use Symfony\Component\Yaml\Yaml;
use Config;
use Order;
use Item;
use Waybill;
use Recompany\Sourcemanager\Sourcemanager;

class ManageOrderStatus extends Sourcemanager implements SourcemanagerWorkerInterface {
    public function __construct(){
        $this->_permissions_resources = Yaml::parse(file_get_contents(Config::get('sourcemanager::config.order_status_path')));
    }

    public function checkUri($uri, $method) {
                                    // Если без url без id - работа не с проектом - не обрабатываем
        if(!$this->_received_id || !$this->_type_received_id) { return true; }
                                    // Достаем статус заявки
        $order_status = $this->_getOrderStatus($this->_received_id, $this->_type_received_id);
        if($order_status) $this->_kitPermission = $order_status; else return true;
        $this->_calculateURLs();
                                    // Проверяем доступ по статусу
        return $this->_isAllow($uri, $method);
    }

    public function getIds(){
                                    // Если работаем не с заявкой - пустой массив
        if(!$this->_kitPermission) return [];
        $this->_calculateIDs();
        return $this->_IDs;
    }
                                        // Достаем статус заявки
    public function _getOrderStatus($id, $type_id) {
        if('order_id' == $type_id) {
            return Order::getStatus($id);
        }
        if('item_id' == $type_id) {
            return Order::getStatus(Item::getOrderId($id));
        }
        if('waybill_id' == $type_id) {
            return Waybill::getWaybillOrderStatus($id);
        }
        return false;
    }
}
