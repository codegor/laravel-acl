<?php
/*
 * This file is part of acl.
 *
 * (c) Egor <codeegor@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Codegor\Acl\Providers;

use Illuminate\Support\ServiceProvider;
use Codegor\Acl\Acl;

class AclServiceProvider extends ServiceProvider {
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/../../config/config.php');
        $this->publishes([$path => config_path('acl.php')], 'config');
        
    }
    public function register(){

		$this->app->bind('codegor.acl', function (){
		  return new Acl(app('router'));
		});
	  }
}