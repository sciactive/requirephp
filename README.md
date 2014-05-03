RequirePHP
==========

An implementation of dependency injection (like RequireJS) in PHP.

Getting Started
---------------

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
	// providing functionality.
	return new test();
});
```

You can keep using the same module in other code.

```php
$require(array('test'), function($test){
	$test->talk(); // Prints '<p>Hello, world.</p>'.
});
```

You can also retrieve modules outside of a closure. However, if this module is not available at the time you request it, RequirePHP will throw a RequireModuleFailedException. Such is the price of not using a closure.

```php
$test = $require('test');
$test->talk(); // Prints '<p>Hello, world.</p>'.
```