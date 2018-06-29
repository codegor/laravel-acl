<?php
  /**
   * Created by PhpStorm.
   * User: esbwo
   * Date: 19.06.2018
   * Time: 0:03
   */
  
  namespace Codegor\Acl;

  use Illuminate\Routing\Route;
  use Illuminate\Routing\Router;
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\DB;
  
  class Acl {
    
    protected $conf = [];
    protected $source = 'config';
    protected $db_table = 'permissions';
    protected $permissions = [];
    protected $state = null;
    
    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;
    
    /**
     * An array of all the registered routes.
     *
     * @var \Illuminate\Routing\RouteCollection
     */
    protected $routes;
    
    public function __construct(Router $router) {
      $this->conf = config('acl.config');
      
      $this->source = $this->conf['role_source'];
      
      if ('DB' == $this->source)
        $this->db_table = $this->conf['db_table'];
      
      if ('config' == $this->source)
        $this->permissions = config('acl.permissions');
  
      $this->state = config('acl.state');
      
      $this->router = $router;
      $this->routes = $router->getRoutes();
    }
    
    public function getPermissions(object $user) {
      $role = $this->getRole($user);
      $res = $this->permissions[$role];
      // TODO filter state with permitted resources
      $res->state = $this->state;
      return $res;
    }
    
    protected function getRole(object $user): string {
      throw_if(!is_object($user), 'ACL PROCESSOR: 1 param should be class of user (model)');
      throw_if(!method_exists($user, 'getRole'), 'ACL PROCESSOR: Class of user (model) hasn\'t trait ACL or should be realized method getRole');
      
      $role = $user->getRole();
      if ('DB' == $this->source && !isset($this->permissions[$role]))
        $this->getPermissionsFromDb($role);
      
      return $role;
    }
    
    public function canAccess(object $user, string $routName, array $params): bool {
      $role = $this->getRole($user);
      if(isset($this->permissions[$role]))
        return $this->hasAccess($this->permissions[$role]->list, $this->permissions[$role]->type, $routName) &&
          $this->isByStatusCanAccess($routName, $params, $role);
      else {
        echo 'permission does not created for role!';
        return false;
      }
      
    }
    
    /**
     * 'manager' => (object) [
     * 'role' => 'manager',
     * 'type' => 'all deny',
     * 'list' => [
     * 'guests.*'
     * ]
     * ]
     */
    private function hasAccess(array $list, string $type, string $route): bool {
      $route = explode('.', $route);
      $has = false;
      
      foreach ($list as $item) {
        $item = explode('.',$item);
        
        if ($route[0] == $item[0]) {
          if (isset($route[1], $item[1])) {
            if('_menu' == $item[1])
              $item[1] = 'index';
            
            if ('*' == $item[1] || $route[1] == $item[1]) {
              $has = true;
              break;
            }
          }
          if (!isset($route[1]) && !isset($item[1])) {
            $has = true;
            break;
          }
        }
      }
      unset($item);
      
      // if type 'all allow' $list contein the list of deny point, if type 'all deny' - $list contein the list of allow point
      if (('all deny' == $type && true == $has) || ('all allow' == $type && false == $has))
        return true;
      
      return false;
      
    }
  
    private function isByStatusCanAccess(string $route, array $params, string $role) {
      $route = explode('.', $route);
      $has = true;
      
      if(!isset($route[1])) // we work only with rosourece with action (resource.action)
        return true;
      if([] === $params) // checks if without params request
        return true;
      if(!isset($this->state[$route[0]])) // checks if for this resourses we have not rules for statuses
        return true;
    
      $statuses = $this->state[$route[0]];
      $work = function(array $resource_statuses) use ($route, $params, $role) : bool { //return false - deny access, return true - allow
        $field = (isset($resource_statuses->_model_field)) ? $resource_statuses->_model_field : 'status';
        $model = (isset($resource_statuses->_model)) ? $resource_statuses->_model : substr($route[0], 0, -1);
        $statuses = (isset($resource_statuses->_statuses)) ? $resource_statuses->_statuses : $resource_statuses;
        if(!isset($params[$model])) {
          echo 'you have error in the status list, ACL cannot determinate model for resource';
          return true; // maybe false, but true is swich off this function,
        }
        if(!isset($params[$model]->{$field})) {
          echo 'you have error in the status list, ACL cannot determinate field of model for status (defoult name is "status")';
          return true; // maybe false, but true is swich off this function,
        }
        $status = $params[$model]->{$field};

        if(!isset($statuses[$status]))
          return true;
        
        $list = $statuses[$status];
        $type = (isset($list['_type'])) ? $list['_type'] : 'all allow';
        $list = (isset($list['_list'])) ? $list['_list'] : $list;
        
        $filtered = [];
        foreach ($list as $item) {
          if(is_string($item))
            $filtered[] = $item;
          else if(is_array($item) && !in_array($role, $item[1])){
            $filtered[] = $item[0];
          }
        }
        unset($item);
        
        $list = $filtered;
        $has = in_array($route[1], $list);

        // if type 'all allow' (default) $list contein the list of deny point, if type 'all deny' - $list contein the list of allow point
        if (('all deny' == $type && true == $has) || ('all allow' == $type && false == $has))
          return true;
  
        return false;
      };
      if ([] === $statuses)
        return true;
      else
        $status_simply = array_keys($statuses) !== range(0, count($statuses) - 1); // check is assoc array or not
    
      if($status_simply)
        $has = $work($statuses);
      else {
        foreach ($statuses as $item)
          $has = ($work($item)) ? $has : false;
        unset($item);
      }

      return $has;
    }
    
    private function getPermissionsFromDb(string $role): void {
      $res = DB::table($this->db_table)->where('role', $role)->first();
      if (!empty($res)) {
        $res->list = json_decode($res->list);
        $this->permissions[$res->role] = $res;
      }
    }
    
    public function getPointsApp() {
      return $this->getRoutes();
    }
    
    protected function getRoutes() {
      $routes = collect($this->routes)->map(function ($route) {
        return $this->getRouteInformation($route);
      })->all();
      
      return array_filter($routes);
    }
    
    protected function getRouteInformation(Route $route) {
      return $this->filterRoute([
        'name'       => $route->getName(),
        'method'     => implode('|', $route->methods()),
        'uri'        => $route->uri(),
        'action'     => ltrim($route->getActionName(), '\\'),
        'middleware' => $this->getMiddleware($route),
      ]);
    }
    
    /**
     * Filter the route by URI and / or name.
     *
     * @param  array $route
     * @return array|null
     */
    protected function filterRoute(array $route) {
      if (!Str::contains($route['middleware'], 'acl'))
        return;
      
      return $route;
    }
    
    protected function getMiddleware($route) {
      return collect($route->gatherMiddleware())->map(function ($middleware) {
        return $middleware instanceof Closure ? 'Closure' : $middleware;
      })->implode(',');
    }
  }