<?php 
namespace Recompany\Sourcemanager;

use Illuminate\Support\ServiceProvider;
use Recompany\Sourcemanager\Workers\ManageRole;

class SourcemanagerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('recompany/sourcemanager');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
                $this->app['sourcemanager'] = $this->app->share(function($app){
                    $user = $app['auth']->user();
                    return 'SourcemanagerServiceProvaider';
                });
                $this->app->bind('sourcemanager', function () {
                    Sourcemanager::getInstance();
                    Sourcemanager::createManageRole();
                    Sourcemanager::createManageProjectStatus();
                    Sourcemanager::createManageOrderStatus();
                    return Sourcemanager::getInstance();
                });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
