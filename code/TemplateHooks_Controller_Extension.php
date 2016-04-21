<?php

/**
 * Copyright 2016 memdev.
 * http://www.memdev.de
 *
 * This piece of code is provided "as is", without any guarantee.
 * Use at your own risk.
 */
class TemplateHooks_Controller_Extension extends Extension implements TemplateGlobalProvider
{

    private $collected = false;

    /**
     * Call this method in templates to allow injection of (template) data at this point.
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
