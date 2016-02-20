<?php
/**
 * Joomla! API Application
 *
 * @copyright  Copyright (C) 2016 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Joomla! API Application class
 *
 * @since  1.0
 */
final class JApplicationApi extends JApplicationCms
{
	/**
	 * The client identifier.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $clientId = 3;

	/**
	 * The name of the application.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $name = 'api';

	/**
	 * Class constructor.
	 *
	 * @param   JInput                 $input   An optional argument to provide dependency injection for the application's
	 *                                          input object.  If the argument is a JInput object that object will become
	 *                                          the application's input object, otherwise a default input object is created.
	 * @param   Registry               $config  An optional argument to provide dependency injection for the application's
	 *                                          config object.  If the argument is a Registry object that object will become
	 *                                          the application's config object, otherwise a default config object is created.
	 * @param   JApplicationWebClient  $client  An optional argument to provide dependency injection for the application's
	 *                                          client object.  If the argument is a JApplicationWebClient object that object will become
	 *                                          the application's client object, otherwise a default client object is created.
	 *
	 * @since   1.0
	 */
	public function __construct(JInput $input = null, Registry $config = null, JApplicationWebClient $client = null)
	{
		// Execute the parent constructor
		parent::__construct($input, $config, $client);

		// Set the root in the URI based on the application name
		JUri::root(null, str_ireplace('/' . $this->getName(), '', JUri::base(true)));

		// Create the application router
		$this->router = new ApiRouter($this, $this->input);
		$this->router->setControllerPrefix('ApiController');
		$this->router->addMap(':component/:type', 'List');
		$this->router->addMap(':component/:type/:id', 'Item');
	}

	/**
	 * Method to run the Web application routines.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		$controller = $this->router->getController($this->get('uri.route'));
	}

	/**
	 * Gets the client id of the current running application.
	 *
	 * @return  integer  A client identifier.
	 *
	 * @since   1.0
	 */
	public function getClientId()
	{
		return $this->clientId;
	}

	/**
	 * Return a reference to the JMenu object.
	 *
	 * Note: The API application defaults to using a JMenuSite object for extension compatibility
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JMenu  JMenu object.
	 *
	 * @since   1.0
	 */
	public function getMenu($name = 'site', $options = array())
	{
		// Inject an application object into the JMenu tree if one isn't already specified
		if (!isset($options['app']))
		{
			$options['app'] = static::getInstance($name);
		}

		return parent::getMenu($name, $options);
	}

	/**
	 * Gets the name of the current running application.
	 *
	 * @return  string  The name of the application.
	 *
	 * @since   1.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the application parameters
	 *
	 * @param   string  $option  The component option
	 *
	 * @return  Registry  The parameters object
	 *
	 * @since   1.0
	 */
	public function getParams($option = null)
	{
		static $params = array();

		$hash = '__default';

		if (!empty($option))
		{
			$hash = $option;
		}

		if (!isset($params[$hash]))
		{
			// Get component parameters
			if (!$option)
			{
				$option = $this->input->getCmd('option', null);
			}

			// Get new instance of component global parameters
			$params[$hash] = clone JComponentHelper::getParams($option);

			// Get menu parameters
			$menus = $this->getMenu();
			$menu  = $menus->getActive();

			// Get language
			$lang_code = $this->getLanguage()->getTag();
			$languages = JLanguageHelper::getLanguages('lang_code');

			$title = $this->get('sitename');

			if (isset($languages[$lang_code]) && $languages[$lang_code]->metadesc)
			{
				$description = $languages[$lang_code]->metadesc;
			}
			else
			{
				$description = $this->get('MetaDesc');
			}

			$rights = $this->get('MetaRights');
			$robots = $this->get('robots');

			// Retrieve com_menu global settings
			$temp = clone JComponentHelper::getParams('com_menus');

			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu))
			{
				// Get show_page_heading from com_menu global settings
				$params[$hash]->def('show_page_heading', $temp->get('show_page_heading'));

				$temp = new Registry;
				$temp->loadString($menu->params);
				$params[$hash]->merge($temp);
				$title = $menu->title;
			}
			else
			{
				// Merge com_menu global settings
				$params[$hash]->merge($temp);

				// If supplied, use page title
				$title = $temp->get('page_title', $title);
			}

			$params[$hash]->def('page_title', $title);
			$params[$hash]->def('page_description', $description);
			$params[$hash]->def('page_rights', $rights);
			$params[$hash]->def('robots', $robots);
		}

		return $params[$hash];
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * Note: The API application defaults to using a JPathwaySite object for extension compatibility
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JPathway  A JPathway object
	 *
	 * @since   1.0
	 */
	public function getPathway($name = 'site', $options = array())
	{
		return parent::getPathway($name, $options);
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * Note: The API application defaults to using a JRouterSite object for extension compatibility
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return	JRouter
	 *
	 * @since	1.0
	 */
	public static function getRouter($name = 'site', array $options = array())
	{
		$options['mode'] = JFactory::getConfig()->get('sef');

		return parent::getRouter($name, $options);
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @param   boolean  $params  True to return the template parameters
	 *
	 * @return  string  The name of the template.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function getTemplate($params = false)
	{
		// The API application should not need to use a template
		return 'system';
	}

	/**
	 * Overrides the default template that would be used
	 *
	 * @param   string  $template     The template name
	 * @param   mixed   $styleParams  The template style parameters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setTemplate($template, $styleParams = null)
	{
		// Leave this here for extension compatibility only
	}
}
