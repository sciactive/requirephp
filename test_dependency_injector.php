<?php namespace SciActive;
// An example of how you could use RequirePHP for dependency injection.
// Keep in mind, this isn't necessarily something you'd do in production, just an example.

// Try giving the query string ?input=badthing to this script in prod mode.
// Also try turning off your network connection, so the script can't reach Google.

require("src/RequirePHP.php");
$test = $_REQUEST['test'] == "true";

// EntryPoint.php
RequirePHP::_('EntryPoint', array(), function(){
  class EntryPoint {
    private $model1;
    private $model2;

    public function __construct($model1, $model2) {
      $this->model1 = $model1;
      $this->model2 = $model2;
    }

    // When we run in "test mode" we only want to test this code, not the models'
    // code. This example uses dependency injection to provide test implementations.
    public function start() {
      $thing1 = $this->model1->getThing();
      $thing2 = $this->model2->getThing();
      if ($thing1 && $thing2) {
        echo "Cool, all things are good.";
      } else {
        echo "Uh oh, things aren't good.";
      }
    }
  }
  return function($model1, $model2){
    return new EntryPoint($model1, $model2);
  };
});

// Model1.php
RequirePHP::_('Model1', array(), function(){
  class Model1 {
    public function getThing() {
      return @\file_get_contents('http://google.com/');
    }
  }
  return function(){
    return new Model1();
  };
});

// Model1Test.php
RequirePHP::_('Model1Test', array(), function(){
  class Model1 {
    public function getThing() {
      return "copy of known good Google html";
    }
  }
  return function(){
    return new Model1();
  };
});

// Model2.php
RequirePHP::_('Model2', array(), function(){
  class Model2 {
    private $helper;

    public function __construct($helper) {
      $this->helper = $helper;
    }

    public function getThing() {
      return $this->helper->getInput() != 'badthing';
    }
  }
  return function($helper){
    return new Model2($helper);
  };
});

// Helper.php
RequirePHP::_('Helper', array(), function(){
  class Helper {
    public function getInput() {
      return $_REQUEST['input'];
    }
  }
  return function(){
    return new Helper();
  };
});

// HelperTest.php
RequirePHP::_('HelperTest', array(), function(){
  class Helper {
    public function getInput() {
      return 'goodthing';
    }
  }
  return function(){
    return new Helper();
  };
});

// Composition root. Probably your main script.
RequirePHP::_(array(), function()use($test){
  $EntryPoint = RequirePHP::_("EntryPoint");

  // Here is where you choose what to pass to your EntryPoint.
  // In this case, we'll be passing real classes vs test classes.
  if (!$test) {
    // Here could be your normal code.
    $Model1 = RequirePHP::_("Model1");
    $Model2 = RequirePHP::_("Model2");
    $Helper = RequirePHP::_("Helper");
  } else {
    // And here could be your test code.
    $Model1 = RequirePHP::_("Model1Test");
    $Model2 = RequirePHP::_("Model2");
    $Helper = RequirePHP::_("HelperTest");
  }

  echo 'This script checks two thing: the "input" request var, and the result of a request to http://google.com/.<br>';
  echo '<a href="?test=true&amp;input=goodthing">Test mode. Good thing.</a><br>';
  echo '<a href="?test=true&amp;input=badthing">Test mode. Bad thing. (In test mode, this shouldn\'t matter.)</a><br>';
  echo '<a href="?test=false&amp;input=goodthing">Prod mode. Good thing.</a><br>';
  echo '<a href="?test=false&amp;input=badthing">Prod mode. Bad thing.</a><br>';
  echo '<br>';

  $entryPoint = $EntryPoint($Model1(), $Model2($Helper()));
  $entryPoint->start();
});
