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
      
      $this->router = $router;
      $this->routes = $router->getRoutes();
    }
    
    public function getPermissions(object $user) {
      $role = $this->getRole($user);
      return $this->permissions[$role];
    }
    
    protected function getRole(object $user): string {
      throw_if(!is_object($user), 'ACL PROCESSOR: 1 param should be class of user (model)');
      throw_if(!method_exists($user, 'getRole'), 'ACL PROCESSOR: Class of user (model) hasn\'t trait ACL or should be realized method getRole');
      
      $role = $user->getRole();
      if ('DB' == $this->source && !isset($this->permissions[$role]))
        $this->getPermissionsFromDb($role);
      
      return $role;
    }
    
    public function canAccess(object $user, string $routName): bool {
      $role = $this->getRole($user);
      
      return $this->hasAccess($this->permissions[$role]->list, $this->permissions[$role]->type, $routName);
      
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
    private function hasAccess(array $list, string $type, $route): bool {
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