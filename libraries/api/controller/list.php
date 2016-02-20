<?php
/**
 * Joomla! API Application
 *
 * @copyright  Copyright (C) 2016 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Controller processing requests for lists
 *
 * @since  1.0
 *
 * @method  JApplicationApi  getApplication()  getApplication()  Get the application object.
 */
class ApiControllerList extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  UnexpectedValueException
	 */
	public function execute()
	{
		// Load the requested model's lookup path into the system
		$option = $this->getInput()->getCmd('option');
		$view   = $this->getInput()->getWord('view');

		$modelPath = JPATH_ROOT . "/components/$option/models";

		JModelLegacy::addIncludePath($modelPath);

		// Try to fetch our model now
		$classPrefix = ucfirst($this->getInput()->getWord('component')) . 'Model';

		/** @var JModelList $model */
		$model = JModelLegacy::getInstance(ucfirst($view), $classPrefix);

		if ($model === false)
		{
			throw new UnexpectedValueException('Model not found.');
		}

		$items = $model->getItems();

		// Load the items into the document's buffer
		$this->getApplication()->getDocument()->setBuffer($items);

		return true;
	}
}
