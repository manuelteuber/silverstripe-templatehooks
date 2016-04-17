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

class TemplateHooks_Controller_Extension extends Extension implements TemplateGlobalProvider
{

	private $collected = false;

	/**
	 * Call this method in templates to allow injection of (template) data at this point.
	 * @param string $hook the name of the hook to be executed
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function TemplateHook()
	{
		$args = func_get_args();
		if (!func_num_args()) {
			throw new \InvalidArgumentException('Required parameter $hook is missing.');
		}
		$hook = $args[0];

		$hooks = Config::inst()->get('TemplateHooks_Extension', 'hooks');
		$results = array();
		if (!empty($hooks[$hook])) {
			uasort($hooks[$hook], function ($a, $b) {
				return $b['Priority'] - $a['Priority'];
			});
			foreach ($hooks[$hook] as $subscriber) {
				/** @var Object $obj */
				$obj = $subscriber['Instance'];
				$method = $subscriber['Method'];
				$result = null;
				if (is_callable($method)) {
					$result = call_user_func_array($method, $args);
				} else {
					if (is_object($obj) && (method_exists($obj, $method) || ($obj instanceof Object && $obj->hasMethod($method)))) {
						$result = call_user_func_array(array($obj, $method), $args);
					}
				}
				if (is_scalar($result)) {
					$results[] = $result;
				} else {
					if (is_object($result) && $result->hasMethod('forTemplate')) {
						$results[] = $result->forTemplate();
					}
				}
			}
		}
		return implode(' ', $results);
	}

	public function onAfterInit()
	{
		if (!$this->collected) {
			$this->collectHooks();
			$this->collected = true;
		}
	}

	/**
	 * Collect global hooks
	 */
	public function collectHooks()
	{
		$implementors = ClassInfo::implementorsOf('TemplateHooks');
		if (!empty($implementors)) {
			foreach ($implementors as $implementor) {
				singleton($implementor)->initHooks();
			}
		}
	}

	/**
	 * Provide a global template variable $TemplateHook
	 * @return array
	 */
	public static function get_template_global_variables()
	{
		return array(
			'TemplateHook' => array(
				'method' => 'TemplateHook',
				'casting' => 'HTMLText'
			)
		);
	}

}
