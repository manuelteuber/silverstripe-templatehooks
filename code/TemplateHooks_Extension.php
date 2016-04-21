<?php

/**
 * Copyright 2016 memdev.
 * http://www.memdev.de
 *
 * This piece of code is provided "as is", without any guarantee.
 * Use at your own risk.
 */
class TemplateHooks_Extension extends DataExtension
{

	private static $hooks = array();

	/**
	 * @param string|array $hook the hook you would like to subscribe to
	 * @param array|callable|string $method the function that should be executed when the hook is called.
	 * Can be an array($object, 'methodName') or a closure function($hook){} or a method name of the calling object.
	 * @param int $priority the priority (higher will be processed earlier - good for sorting)
	 */
	public function hookInto($hook, $method, $priority = 50)
	{
		if (is_array($method) && count($method) >= 2) {
			$obj = $method[0];
			$method = $method[1];
		} else {
			$obj = $this->getOwner();
		}
		if (!is_object($obj) && !is_callable($method)) {
			return;
		}
		$hooks = Config::inst()->get(get_class(), 'hooks');
		if (!is_object($obj)) {
			$hash = 'function-';
		} else {
			$hash = get_class($obj);
		}
		if (is_callable($method)) {
			$hash .= uniqid();
		} else {
			$hash .= (string)$method;
		}
		foreach ((array)$hook as $key) {
			if (!isset($hooks[$key][$hash])) {
				$hooks[$key][$hash] = array(
					'Instance' => $obj,
					'Method' => $method,
					'Priority' => (int)$priority
				);
			}
		}

		Config::inst()->update(get_class(), 'hooks', $hooks);
	}
}
