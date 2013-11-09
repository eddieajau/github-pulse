<?php
/**
 * Mustache service provider.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pulse\Providers;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Registers the Mustache service provider.
 *
 * @since  1.0
 */
class MustacheServiceProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider within a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register(Container $container)
	{
		$container->set('mustache', function(Container $c) {

			$config = $c->get('config');

			$mustache = new \Mustache_Engine(array(
				'loader' => new \Mustache_Loader_FilesystemLoader(
					$config->get('mustache.views', __DIR__ . '/../views'),
					array(
						'extension' => $config->get('mustache.ext', '.md'),
					)
				),
			));

			$mustache->addHelper('number', array(
				'1f' => function ($value)
				{
					return sprintf('%.1f', $value);
				},
			));

			return $mustache;
		});
	}
}
