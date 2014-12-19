<?php namespace SciActive;
// An example of how you could use RequirePHP as a service locator.

require("src/R.php");

// Define service.
R::_('service', array(), function(){
	class service {
		private $value = 0;

		public function increment() {
			$this->value++;
		}

		public function printOut() {
			echo $this->value;
		}
	}

	return new service();
});

// Locate the service.
R::_(array('service'), function(service $service){
	$service->increment();
});

// Locate the service again.
R::_(array('service'), function(service $service){
	$service->increment();
	echo 'You should see "2".<br>';
	$service->printOut();
});
