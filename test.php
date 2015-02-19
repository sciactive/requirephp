<?php namespace SciActive;

error_reporting(E_ALL);

// Test making the max depth huuuuge. (Bigger than PHP recursion limit.)
//define('REQUIREPHP_MAX_DEPTH', 600);

require("src/RequirePHP.php");

// Use test.
RequirePHP::_(array('test'), function($test){
	$test->value = '<p>It works!!</p>';
});


// Define test.
RequirePHP::_('test', array('depend', 'depend3'), function($d, $d2){
	if (!$d || !$d2) {
		echo '<p>Depend isn\'t working!!</p>';
	}
	class test {
		public $value;

		public function talk() {
			echo $this->value;
		}
	}

	return new test();
});


// Define the dependencies of test.
RequirePHP::_('depend', array(), function(){
	return true;
});

// Test aliases
RequirePHP::alias('depend3', 'depend2');
RequirePHP::_('depend2', array(), function(){
	// This is used to check that dependencies are being loaded correctly.
	//return false;
	return true;
});


// Use test again.
RequirePHP::alias('toast', 'test');
RequirePHP::alias('taste', 'toast');
RequirePHP::_(array('taste'), function($test){
	// Check the require('thing') syntax.
	$d = RequirePHP::_('depend');
	$d2 = RequirePHP::_('depend2');
	if (!$d || !$d2) {
		echo '<p>Depend isn\'t working!!</p>';
	}
	// This makes sure we're getting the same instance, and not a copy, of $test.
	$test->talk();
});


// Use test outside of a closure.
RequirePHP::_('test')->talk();


// Let's test circular dependencies.
try {
	RequirePHP::_('circ1', array('circ2'), function($circ2){
		return;
	});
	RequirePHP::_('circ2', array('circ1'), function($circ1){
		return;
	});
	RequirePHP::_(array('circ1'), function($circ1){
		echo '<p>This shouldn\'t have run!!</p>';
	});
} catch (RequireTooDeepException $e) {
	RequirePHP::undef('circ1');
	echo '<p>Circular dependencies don\'t crash the script!! Yay!! See the message: '.$e->getMessage().'</p>' ;
}

// Let's test alias and module removal.
RequirePHP::_('removemodule', array(), function(){
	echo '<p>Uh oh. Module removal failed. :(</p>';
});
RequirePHP::alias('removealias', 'removemodule');

RequirePHP::undefAlias('removealias');
RequirePHP::_(array('removealias'), function(){
	echo '<p>Uh oh. Alias removal failed. :(</p>';
});

$failed = false;
RequirePHP::undef('removemodule');
RequirePHP::_(array('removemodule'), function(){
	global $failed;
	$failed = true;
	return;
});

if (!$failed) {
	echo '<p>Looks like alias and module removal passed! :)</p>';
}