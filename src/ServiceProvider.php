<?php namespace OpentokLaravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenTok\OpenTok as OpenTokApi;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom($this->configPath(), 'opentok');
	}

	public function boot()
	{
		$this->publishes([$this->configPath() => config_path('opentok.php')], 'config');

		$this->app->singleton('Opentok', function($app) {
			return new OpenTokApi(
				$app['config']->get('opentok')['api_key'],
				$app['config']->get('opentok')['api_secret']
			);
		});
	}

	protected function configPath()
	{
		return __DIR__ . '/../config/opentok.php';
	}

}
