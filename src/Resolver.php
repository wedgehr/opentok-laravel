<?php

namespace OpentokLaravel;

use OpentokLaravel\Exceptions\ProjectNotFoundException;
use OpenTok\OpenTok as OpenTokApi;
use Illuminate\Contracts\Foundation\Application as App;

/**
 * Opentok resolver is responsible for resolving multiple Opentok projects
 */
class Resolver {
	/**
	 * The application instance.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * The name of the default Opentok project
	 * @var string
	 */
	protected $default;

	/**
	 * The defined Opentok projects
	 *
	 * array
	 *
	 *
	 * @var array (see above)
	 */
	protected $projects = [];

	/**
	 * Create a new opentok resolver instance
	 *
	 * @param \Illuminate\Contracts\Foundation\Application $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		$this->app = $app;

		$config = $this->app['config']->get('opentok');

		$this->default = $config['default'];
		$this->projects = $config['projects'];

		$this->initProjects();
	}

	/**
	 * Resolve an Opentok project by name
	 *
	 * @param string $name
	 */
	public function project($name)
	{
		// ensure we have a project with this name
		if (! array_key_exists($name, $this->projects)) {
			// check if perhaps we were passed an ApiKey as the name
			if (is_numeric($name)) {
				return $this->projectByKey($name);
			}

			throw new ProjectNotFoundException(sprintf('Opentok Project "%s" is not defined', $name));
		}

		return $this->app->make(sprintf('Opentok-%s', $name));
	}

	/**
	 * Resolve a project by its API Key
	 *
	 * @param integer $apiKey
	 */
	public function projectByKey($apiKey)
	{
		foreach ($this->projects as $name => $project) {
			// lazy eval the keys
			if ($project['api-key'] == $apiKey) {
				return $this->app->make(sprintf('Opentok-%s', $name));
			}
		}

		throw new ProjectNotFoundException(sprintf('Opentok Project with ApiKey "%d" is not defined', $apiKey));
	}

	/**
	 * Helper method to the default project
	 */
	public function defaultProject()
	{
		return $this->project($this->default);
	}

	/**
	 * Proxy calls to the default instance
	 *
	 * @param string $name the method name that was called
	 * @param array $args an array of argumes passed to the method
	 */
	public function __call($name, $args)
	{
		return call_user_func_array([$this->defaultProject(), $name], $args);
	}

	/**
	 * Init and bind an OpenTok API instance for each project we have defined
	 */
	private function initProjects()
	{
		foreach ($this->projects as $name => $project) {
			$this->app->singleton(sprintf('Opentok-%s', $name), function($app) use ($project) {
				return new OpenTokApi(
					$project['api-key'],
					$project['api-secret']
				);
			});
		}
	}
}
