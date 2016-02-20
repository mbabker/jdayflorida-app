<?php
/**
 * Joomla! API Application
 *
 * @copyright  Copyright (C) 2016 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

// Joomla system checks.
@ini_set('magic_quotes_runtime', 0);

// System includes
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Register the library base path for the application Joomla compatibility libraries.
JLoader::registerPrefix('J', dirname(__DIR__) . '/libraries/joomla', false, true);

// Register the library base path for the application libraries.
JLoader::registerPrefix('Api', dirname(__DIR__) . '/libraries/api');

// Set system error handling
JError::setErrorHandling(E_NOTICE, 'callback', array('ApiError', 'handleLegacyError'));
JError::setErrorHandling(E_WARNING, 'callback', array('ApiError', 'handleLegacyError'));
JError::setErrorHandling(E_ERROR, 'callback', array('ApiError', 'handleLegacyError'));

// Set a custom Exception handler
restore_exception_handler();
set_exception_handler(array('ApiError', 'handleUncaughtThrowable'));

// Register the application client
JApplicationHelper::addClientInfo((object) array('id' => 3, 'name' => 'api', 'path' => JPATH_BASE));

$version = new JVersion;

// Installation check, and check on removal of the install directory.
if (!file_exists(JPATH_CONFIGURATION . '/configuration.php')
	|| (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
	|| (file_exists(JPATH_INSTALLATION . '/index.php') && (false === $version->isInDevelopmentState())))
{
	{
		echo 'No configuration file found.  Please ensure you have installed Joomla before using this application.';

		exit;
	}
}

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// System configuration.
$config = new JConfig;

// Set the error_reporting
switch ($config->error_reporting)
{
	case 'default':
	case '-1':
		break;

	case 'none':
	case '0':
		error_reporting(0);

		break;

	case 'simple':
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set('display_errors', 1);

		break;

	case 'maximum':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		break;

	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);

		break;

	default:
		error_reporting($config->error_reporting);
		ini_set('display_errors', 1);

		break;
}

define('JDEBUG', $config->debug);

unset($config);

// System profiler
if (JDEBUG)
{
	$_PROFILER = JProfiler::getInstance('Application');
}
