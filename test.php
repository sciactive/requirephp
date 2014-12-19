<?php namespace SciActive;

error_reporting(E_ALL);

// Test making the max depth huuuuge. (Bigger than PHP recursion limit.)
//define('REQUIREPHP_MAX_DEPTH', 600);

require("src/R.php");

// Use test.
R::_(array('test'), function($test){
	$test->value = '<p>It works!!</p>';
});


// Define test.
R::_('test', array('depend', 'depend3'), function($d, $d2){
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
R::_('depend', array(), function(){
	return true;
});

// Test aliases
R::alias('depend3', 'depend2');
R::_('depend2', array(), function(){
	// This is used to check that dependencies are being loaded correctly.
	//return false;
	return true;
});


// Use test again.
R::alias('toast', 'test');
R::alias('taste', 'toast');
R::_(array('taste'), function($test){
	// Check the require('thing') syntax.
	$d = R::_('depend');
	$d2 = R::_('depend2');
	if (!$d || !$d2) {
		echo '<p>Depend isn\'t working!!</p>';
	}
	// This makes sure we're getting the same instance, and not a copy, of $test.
	$test->talk();
});


// Use test outside of a closure.
R::_('test')->talk();


// Let's test circular dependencies.
try {
	R::_('circ1', array('circ2'), function($circ2){
		return;
	});
	R::_('circ2', array('circ1'), function($circ1){
		return;
	});
	R::_(array('circ1'), function($circ1){
		echo '<p>This shouldn\'t have run!!</p>';
	});
} catch (RequireTooDeepException $e) {
	R::undef('circ1');
	echo '<p>Circular dependencies don\'t crash the script!! Yay!! See the message: '.$e->getMessage().'</p>' ;
}

// Let's test alias and module removal.
R::_('removemodule', array(), function(){
	echo '<p>Uh oh. Module removal failed. :(</p>';
});
R::alias('removealias', 'removemodule');

R::undefAlias('removealias');
R::_(array('removealias'), function(){
	echo '<p>Uh oh. Alias removal failed. :(</p>';
});

$failed = false;
R::undef('removemodule');
R::_(array('removemodule'), function(){
	global $failed;
	$failed = true;
	return;
});

if (!$failed) {
	echo '<p>Looks like alias and module removal passed! :)</p>';
}