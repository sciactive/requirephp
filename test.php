<?php

// Test making the max depth huuuuge. (Bigger than PHP recursion limit.)
//define('REQUIREPHP_MAX_DEPTH', 600);

require("require.php");

// Use test.
RPHP::_(array('test'), function($test){
	$test->value = '<p>It works!!</p>';
});


// Define test.
RPHP::_('test', array('depend', 'depend3'), function($d, $d2){
	if (!$d || !$d2)
		echo '<p>Depend isn\'t working!!</p>';
	class test {
		public $value;

		public function talk() {
			echo $this->value;
		}
	}

	return new test();
});


// Define the dependencies of test.
RPHP::_('depend', array(), function(){
	return true;
});

// Test aliases
RPHP::alias('depend3', 'depend2');
RPHP::_('depend2', array(), function(){
	// This is used to check that dependencies are being loaded correctly.
	//return false;
	return true;
});


// Use test again.
RPHP::alias('toast', 'test');
RPHP::alias('taste', 'toast');
RPHP::_(array('taste'), function($test){
	// Check the require('thing') syntax.
	$d = RPHP::_('depend');
	$d2 = RPHP::_('depend2');
	if (!$d || !$d2)
		echo '<p>Depend isn\'t working!!</p>';
	// This makes sure we're getting the same instance, and not a copy, of $test.
	$test->talk();
});


// Use test outside of a closure.
RPHP::_('test')->talk();


// Let's test circular dependencies.
try {
	RPHP::_('circ1', array('circ2'), function($circ2){
		return;
	});
	RPHP::_('circ2', array('circ1'), function($circ1){
		return;
	});
	RPHP::_(array('circ1'), function($circ1){
		echo '<p>This shouldn\'t have run!!</p>';
	});
} catch (RequireTooDeepException $e) {
	echo '<p>Circular dependencies don\'t crash the script!! Yay!! '.$e->getMessage().'</p>' ;
}
