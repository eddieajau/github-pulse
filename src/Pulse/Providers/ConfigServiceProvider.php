<?php
/**
 * Configuration service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pulse\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * Registers the Configuration service provider.
 *
 * Note that the application requires the `PULSE_CONFIG` constant to be set with the path to the JSON configuration file.
 *
 * @since  1.0
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
	/**
	 * @var    string
	 * @since  1.0
	 */
	private $path;

	/**
	 * Class constructor.
	 *
	 * @param   string  $path  The full path and file name for the configuration file.
	 *
	 * @since   1.0
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \LogicException if the PULSE_CONFIG constant is not defined.
	 */
	public function register(Container $container)
	{
		$path = $this->path;

		$container->share(
			'config',
			function(Container $c) use ($path)
			{
				if (!file_exists($path))
				{
					throw new \LogicException('Configuration file does not exist.', 500);
				}

				$json = json_decode(file_get_contents($path));

				if (null === $json)
				{
					throw new \UnexpectedValueException('Configuration file could not be parsed.', 500);
				}

				$config = new Registry($json);

				return $config;
			},
			true
		);
	}
}
