# <img alt="logo" src="https://raw.githubusercontent.com/sciactive/2be-extras/master/logo/product-icon-40-bw.png" align="top" /> RequirePHP

[![Latest Stable Version](https://img.shields.io/packagist/v/sciactive/requirephp.svg?style=flat)](https://packagist.org/packages/sciactive/requirephp) [![License](https://img.shields.io/packagist/l/sciactive/requirephp.svg?style=flat)](https://packagist.org/packages/sciactive/requirephp) [![Total Downloads](https://img.shields.io/packagist/dt/sciactive/requirephp.svg?style=flat)](https://packagist.org/packages/sciactive/requirephp) [![Open Issues](https://img.shields.io/github/issues/sciactive/requirephp.svg?style=flat)](https://github.com/sciactive/requirephp/issues)

An implementation of dependency injection and service locator (like RequireJS) in PHP.

## Installation

You can install RequirePHP with Composer or Bower.

```sh
composer require sciactive/requirephp

bower install https://github.com/sciactive/requirephp.git
```

## Getting Started

If you don't use an autoloader, all you need to do is include the RequirePHP.php file.

```php
require("RequirePHP.php");
```

Now you can start giving code that requires a module, or modules, to run. This code will not run until all the required modules (in this case, only 'test') are available.

```php
\SciActive\RequirePHP::_(array('test'), function($test){
	$test->value = '<p>Hello, world.</p>';
});
```

You can define modules. This module has no dependencies, hence the empty array.

```php
\SciActive\RequirePHP::_('test', array(), function(){
	class test {
		public $value;

		public function talk() {
			echo $this->value;
		}
	}

	// Returning a new instantiation is important if you are
	// providing a service.
	return new test();
});
```

You can create aliases to modules (and other aliases).

```php
\SciActive\RequirePHP::alias('testing', 'test');
```

You can keep using the same instance in other code, using RequirePHP as a service locator. This function uses the alias from above.

```php
\SciActive\RequirePHP::_(array('testing'), function($test){
	$test->talk(); // Prints '<p>Hello, world.</p>'.
});
```

You can also retrieve modules outside of a closure. However, if this module is not available at the time you request it, RequirePHP will throw a RequireModuleFailedException. Such is the price of not using a closure.

```php
$test = \SciActive\RequirePHP::_('test');
$test->talk(); // Prints '<p>Hello, world.</p>'.
```

## Service Location

The repository contains [an example](https://github.com/sciactive/requirephp/blob/master/test_service_locator.php) of using RequirePHP as a service locator.

## Dependency Injection

The repository contains [an example](https://github.com/sciactive/requirephp/blob/master/test_dependency_injector.php) of using RequirePHP as a dependency injector.

## Contacting the Developer

There are several ways to contact RequirePHP's developer with your questions, concerns, comments, bug reports, or feature requests.

- RequirePHP is part of [SciActive on Twitter](http://twitter.com/SciActive).
- Bug reports, questions, and feature requests can be filed at the [issues page](https://github.com/sciactive/requirephp/issues).
- You can directly [email Hunter Perrin](mailto:hunter@sciactive.com), the creator of RequirePHP.
