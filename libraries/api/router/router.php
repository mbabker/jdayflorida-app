<?php
/**
 * Joomla! API Application
 *
 * @copyright  Copyright (C) 2016 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Basic Web application router class for the Joomla! API application.
 *
 * @since  1.0
 */
class ApiRouter extends JApplicationWebRouterBase
{
	/**
	 * Find the appropriate controller based on a given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  JController
	 *
	 * @since   1.0
	 */
	public function getController($route)
	{
		return $this->fetchController($this->parseRoute($route));
	}
}
