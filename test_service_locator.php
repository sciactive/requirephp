<?php
// An example of how you could use RequirePHP as a service locator.

require("require.php");

// Define service.
RPHP::_('service', array(), function(){
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
RPHP::_(array('service'), function(service $service){
	$service->increment();
});

// Locate the service again.
RPHP::_(array('service'), function(service $service){
	$service->increment();
	echo 'You should see "2".<br>';
	$service->printOut();
});
