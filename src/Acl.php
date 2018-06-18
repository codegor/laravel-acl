<?php
  /**
   * Created by PhpStorm.
   * User: esbwo
   * Date: 19.06.2018
   * Time: 0:03
   */
  
  namespace Codegor\Acl;
  
  use Illuminate\Support\Facades\DB;
  
  class Acl {
    
    protected $conf = [];
    protected $source = 'config';
    protected $db_table = 'permissions';
    protected $permissions = [];
    
    public function __construct() {
      $this->conf = config('acl.config');
      
      $this->source = $this->conf['role_source'];
      
      if ('DB' == $this->source)
        $this->db_table = $this->conf['db_table'];
      
      if ('config' == $this->source)
        $this->permissions = $this->conf['permissions'];
    }
  
    public function getPermissions(object $user) {
      $role = $this->getRole($user);
      return  $this->permissions[$role];
    }
  
    protected function getRole(object $user) {
      throw_if(!is_object($user), 'ACL PROCESSOR: 1 param should be class of user (model)');
      throw_if(!method_exists($user, 'getRole'), 'ACL PROCESSOR: Class of user (model) hasn\'t trait ACL or should be realized method getRole');
  
      $role = $user->getRole();
      if ('DB' == $this->source && !isset($this->permissions[$role]))
        $this->getPermissionsFromDb($role);
      
      return $role;
    }
    
    public function canAccess(object $user, string $routName): bool {
      $role = $this->getRole($user);
      
      return $this->hasAccess($this->permissions[$role]->list, $this->permissions[$role]->type,routName);
      
    }
    
    /**
     * 'manager' => (object) [
          'role' => 'manager',
          'type' => 'all deny',
          'list' => [
            'guests.*'
          ]
        ]
     */
    private function hasAccess(array $list, string $type, $route) : bool {
      $route = explode('.', $route);
      $has = false;
      
      foreach ($list as $item){
        $item = explode($item);
        
        if($route[0] == $item[0]){
          if(isset($route[1], $item[1])){
            if('*' == $item[1] || $route[1] == $item[1]) {
              $has = true;
              break;
            }
          }
          if(!isset($route[1]) && !isset($item[1])){
            $has = true;
            break;
          }
        }
      }
      unset($item);
  
      // if type 'all allow' $list contein the list of deny point, if type 'all deny' - $list contein the list of allow point
      if(('all deny' == $type && true == $has) || ('all allow' == $type && false == $has))
        return true;
      
      return false;
      
    }
    
    private function getPermissionsFromDb(string $role): void {
      $res = DB::table($this->db_table)->where('role', $role)->first();
      if (!empty($res)) {
        $res->list = json_decode($res->list);
        $this->permissions[$res->role] = $res;
      }
    }
  }