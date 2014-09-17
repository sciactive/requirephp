# <img alt="logo" src="https://raw.githubusercontent.com/sciactive/2be-extras/master/logo/product-icon-40-bw.png" align="top" /> RequirePHP

An implementation of dependency injection and service locator (like RequireJS) in PHP.

## Getting Started

All you need to do is include the require.php file, and instantiate your container.

```php
require("require.php");
$require = new RequirePHP();
```

Now you can start giving code that requires a module, or modules, to run. This code will not run until all the required modules (in this case, only 'test') are available.

```php
$require(array('test'), function($test){
	$test->value = '<p>Hello, world.</p>';
});
```

You can define modules. This module has no dependencies, hence the empty array.

```php
$require('test', array(), function(){
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
$require->alias('testing', 'test');
```

You can keep using the same instance in other code, using RequirePHP as a service locator. This function uses the alias from above.

```php
$require(array('testing'), function($test){
	$test->talk(); // Prints '<p>Hello, world.</p>'.
});
```

You can also retrieve modules outside of a closure. However, if this module is not available at the time you request it, RequirePHP will throw a RequireModuleFailedException. Such is the price of not using a closure.

```php
$test = $require('test');
$test->talk(); // Prints '<p>Hello, world.</p>'.
```

## Service Location

The repository contains [an example](https://github.com/sciactive/requirephp/blob/master/test_service_locator.php) of using RequirePHP as a service locator.

## Dependency Injection

The repository contains [an example](https://github.com/sciactive/requirephp/blob/master/test_dependency_injector.php) of using RequirePHP as a dependency injector.

## Chaining

Chaining allows you to make several calls to RequirePHP in the same statement. You can use chaining by calling call() on the return value of RequirePHP and its alias method.

```php
$require('chain', array('message'), function($message){
	class chain {
		private $message;
		public function __construct($message) {
			$this->message = $message;
		}
		public function talk() {
			echo $this->message;
		}
	}
	return new chain($message);
})
->call('message', array(), function(){
	return '<p>Chaining works.</p>';
})
->alias('load', 'chain')
->call('load')->talk(); // Prints '<p>Chaining works.</p>'.
```
