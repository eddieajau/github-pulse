<?php
/**
 * The Pulse application.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pulse;

use Joomla\Application\AbstractCliApplication;
use Joomla\DI\Container;
use Joomla\Registry\Registry;

/**
 * The Pulse application class.
 *
 * @since  1.0
 */
class Application extends AbstractCliApplication
{
	/**
	 * The application version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const VERSION = '1.0.0';

	/**
	 * The application's DI container.
	 *
	 * @var    Di\Container
	 * @since  1.0
	 */
	private $container;

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Check if help is needed.
		if ($this->input->get('h') || $this->input->get('help'))
		{
			$this->out('Github Pulse ' . self::VERSION);
			$this->out('------------------');
			$this->out();
			$this->out('Usage:     php -f pulse.php -- [switches]');
			$this->out();
			$this->out('Switches:  -h | --help    Prints this usage information.');
			$this->out('           --user         The name of the Github user (associated with the repository).');
			$this->out('           --repo         The name of the Github repository.');
			$this->out('           --username     Your Github login username.');
			$this->out('           --password     Your Github login password.');
			$this->out();
			$this->out('Examples:  php -f pulse.php -h');
			$this->out('           php -f pulse.php -- --user=foo --repo=bar');
			$this->out();
		}
		else
		{
			parent::execute();
		}
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		$config = $this->container->get('config');
		$user = $this->input->get('user', $config->get('github.user'));
		$repo = $this->input->get('repo', $config->get('github.repo'));
		$debug = $this->input->getBool('debug');

		if (empty($user) or empty($repo))
		{
			throw new \UnexpectedValueException('A Github user and repository must be provided via the command line or application configuration.');
		}

		$state = new Registry(array(
			'user' => $user,
			'repo' => $repo,
			'months' => $this->input->get('months', 12),
		));

		$model = new Model($this->container->get('github'), $state);
		$model->setLogger($this->container->get('logger'));
		$issues = $model->getIssues($debug);

		$mustache = $this->container->get('mustache');

		$view = $mustache->loadTemplate('report');
		$this->out($view->render(array(
			'issues' => $issues,
		)));
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the AbstractCliApplication::__construct method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function initialise()
	{
		// New DI stuff!
		$container = new Container;
		$input = $this->input;

		$container->share(
			'input',
			function (Container $c) use ($input)
			{
				return $input;
			},
			true
		);

		$container->registerServiceProvider(new Providers\ConfigServiceProvider(PULSE_CONFIG));
		$container->registerServiceProvider(new Providers\GithubServiceProvider);
		$container->registerServiceProvider(new Providers\LoggerServiceProvider);
		$container->registerServiceProvider(new Providers\MustacheServiceProvider);

		$this->container = $container;
	}
}
