<?php namespace SciActive;
// An example of how you could use RequirePHP as a service locator.

require("src/RequirePHP.php");

// Define service.
RequirePHP::_('service', array(), function(){
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
RequirePHP::_(array('service'), function(service $service){
	$service->increment();
});

// Locate the service again.
RequirePHP::_(array('service'), function(service $service){
	$service->increment();
	echo 'You should see "2".<br>';
	$service->printOut();
});
