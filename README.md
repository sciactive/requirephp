# <img alt="logo" src="https://raw.githubusercontent.com/sciactive/2be-extras/master/logo/product-icon-40-bw.png" align="top" /> RequirePHP

An implementation of dependency injection and service locator (like RequireJS) in PHP.

## Getting Started

All you need to do is include the require.php file.

```php
require("R.php");
```

Now you can start giving code that requires a module, or modules, to run. This code will not run until all the required modules (in this case, only 'test') are available.

```php
\SciActive\R::_(array('test'), function($test){
	$test->value = '<p>Hello, world.</p>';
});
```

You can define modules. This module has no dependencies, hence the empty array.

```php
\SciActive\R::_('test', array(), function(){
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
\SciActive\R::alias('testing', 'test');
```

You can keep using the same instance in other code, using RequirePHP as a service locator. This function uses the alias from above.

```php
\SciActive\R::_(array('testing'), function($test){
	$test->talk(); // Prints '<p>Hello, world.</p>'.
});
```

You can also retrieve modules outside of a closure. However, if this module is not available at the time you request it, RequirePHP will throw a RequireModuleFailedException. Such is the price of not using a closure.

```php
$test = \SciActive\R::_('test');
$test->talk(); // Prints '<p>Hello, world.</p>'.
```

## Service Location

The repository contains [an example](https://github.com/sciactive/requirephp/blob/master/test_service_locator.php) of using RequirePHP as a service locator.

## Dependency Injection

The repository contains [an example](https://github.com/sciactive/requirephp/blob/master/test_dependency_injector.php) of using RequirePHP as a dependency injector.
