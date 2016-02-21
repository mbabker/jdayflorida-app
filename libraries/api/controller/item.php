<?php
/**
 * Joomla! API Application
 *
 * @copyright  Copyright (C) 2016 Michael Babker. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Controller processing requests for items
 *
 * @since  1.0
 */
class ApiControllerItem extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		// Load the requested model's lookup path into the system
		$option = $this->getInput()->getCmd('option');
		$view   = $this->getInput()->getWord('view');

		// If the view's name is "category", we treat this as a list and just run the list controller
		if ($view == 'category')
		{
			$controller = new ApiControllerList($this->getInput(), $this->getApplication());

			return $controller->execute();
		}

		$modelPath = JPATH_ROOT . "/components/$option/models";

		JModelLegacy::addIncludePath($modelPath);

		// Try to fetch our model now
		$classPrefix = ucfirst($this->getInput()->getWord('component')) . 'Model';

		/** @var JModelItem $model */
		$model = JModelLegacy::getInstance(ucfirst($view), $classPrefix);

		if ($model === false)
		{
			throw new UnexpectedValueException('Model not found.', 404);
		}

		$item = $model->getItem($this->getInput()->getUint('id'));

		// Load the item into the document's buffer
		$this->getApplication()->getDocument()->setBuffer($item);

		return true;
	}
}
