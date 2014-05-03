<?php

// Test making the max depth huuuuge. (Bigger than PHP recursion limit.)
//define('REQUIREPHP_MAX_DEPTH', 600);

require("require.php");
$require = new RequirePHP();

// Use test.
$require(array('test'), function($test){
	$test->value = '<p>It works!!</p>';
});


// Define test.
$require('test', array('depend', 'depend2'), function($d, $d2){
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
$require('depend', array(), function(){
	return true;
});

$require('depend2', array(), function(){
	return true;
	// This is used to check that dependencies are being loaded correctly.
	//return false;
});


// Use test again.
$require(array('test'), function($test)use($require){
	// Check the require('thing') syntax.
	$d = $require('depend');
	$d2 = $require('depend2');
	if (!$d || !$d2)
		echo '<p>Depend isn\'t working!!</p>';
	// This makes sure we're getting the same instance, and not a copy, of $test.
	$test->talk();
});


// Let's test circular dependencies.
try {
	$require('circ1', array('circ2'), function($circ2){
		return;
	});
	$require('circ2', array('circ1'), function($circ1){
		return;
	});
	$require(array('circ1'), function($circ1){
		echo '<p>This shouldn\'t have run!!</p>';
	});
} catch (RequireTooDeepException $e) {
	echo '<p>Circular dependecies don\'t crash the script!! Yay!! '.$e->getMessage().'</p>' ;
}
