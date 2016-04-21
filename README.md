# Template Hooks for SilverStripe
A simple template hook system for SilverStripe.

[![Latest Stable Version](https://poser.pugx.org/memdev/silverstripe-templatehooks/v/stable?format=flat)](https://packagist.org/packages/memdev/silverstripe-templatehooks)
[![Total Downloads](https://poser.pugx.org/memdev/silverstripe-templatehooks/downloads?format=flat)](https://packagist.org/packages/memdev/silverstripe-templatehooks)
[![Latest Unstable Version](https://poser.pugx.org/memdev/silverstripe-templatehooks/v/unstable?format=flat)](https://packagist.org/packages/memdev/silverstripe-templatehooks)
[![License](https://poser.pugx.org/memdev/silverstripe-templatehooks/license?format=flat)](https://packagist.org/packages/memdev/silverstripe-templatehooks)
[![Build Status](https://travis-ci.org/memdev/silverstripe-templatehooks.svg?branch=master)](https://travis-ci.org/memdev/silverstripe-templatehooks)

Sometimes extending / overriding a template is not enough or would produce a lot of duplicate markup.
Maybe you just want to inject some markup at a specific point in your template file.
This is where template hooks come into play.

With template hooks, you can add named "injection points" everywhere in your SilverStripe template files and hook into
them from within your Controllers or DataObjects.

## Requirements

* silverstripe/framework 3.1+

## Installation

```sh
$ composer require memdev/silverstripe-templatehooks
```

You'll need to do a flush by appending `?flush=1` to your site's URL.

## Usage

To add a hook point to your template, simply call `$TemplateHook()`, providing a name for this hook as the first parameter:

```html
<div>
    <nav class="primary">
        <span class="nav-open-button">²</span>
        <ul>
            <% loop $Menu(1) %>
                <li class="$LinkingMode"><a href="$Link" title="$Title.XML">$MenuTitle.XML</a></li>
            <% end_loop %>
            $TemplateHook('MainNavigation')
        </ul>
    </nav>
    $TemplateHook('AfterMainNavigation')
</div>
```

You can subscribe to this hook by calling `hookInto()` in your Controller or DataObject:

```php
class Page_Controller extends ContentController implements TemplateHooks {

	/**
	 * Use this method to globally subscribe to template hooks.
	 * If you wish to subscribe to hooks in the current controller / object scope,
	 * call "hookInto()" from within any other method, e.g. the controllers init() method.
	 */
	public function initHooks()
	{
		$this->hookInto('MainNavigation', function($hook) {
			return SSViewer::execute_template('MyNavigationAppendix', array());
		});
	}

	public function init() {
		parent::init();

		$this->hookInto('AfterMainNavigation', array($this, 'AfterMainNavigationHook'));

		// OR

		$self = $this;
		$this->hookInto('AfterMainNavigation', function($hook) use ($self) {
		    return "You are currently reading page {$self->Title}";
		});
	}

	public function AfterMainNavigationHook($hook) {
	    return "You are currently reading page {$this->Title}";
	}
}
```

You can also pass parameters with the template hook:

```html
<% loop $Menu(1) %>
    <li class="$LinkingMode">
        <a href="$Link" title="$Title.XML">
            $TemplateHook('MainNavItem', $ID)
            $MenuTitle.XML
        </a>
    </li>
<% end_loop %>
```

It will be available in your subscriber function:

```php
$this->hookInto('MainNavItem', function($hook, $id) {
    $page = Page::get()->byID($id);
    // your code here
}
```

## Documentation

 TODO

## Reporting Issues

Please [create an issue](http://github.com/memdev/silverstripe-templatehooks/issues) for any bugs you've found, or features you're missing.
