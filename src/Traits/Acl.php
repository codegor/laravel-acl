<?php
  /**
   * Created by PhpStorm.
   * User: esbwo
   * Date: 18.06.2018
   * Time: 23:53
   */
  
  namespace Codegor\Acl\Traits;
  
  
  trait Acl {
    public function getRole() {
      $field = 'role';
      if(property_exists($this, 'permission_field'))
        $field = $this->permission_field;
      
      return $this->{$field};
    }
  }