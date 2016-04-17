<?php

/**
 * Copyright 2016 memdev.
 * http://www.memdev.de
 *
 * This piece of code is provided "as is", without any guarantee.
 * Use at your own risk.
 */
interface TemplateHooks
{
	/**
	 * Use this method to globally subscribe to template hooks.
	 * If you wish to subscribe to hooks in the current controller / object scope,
	 * call "hookInto()" from within any other method, e.g. the controllers init() method.
	 */
	public function initHooks();
}
