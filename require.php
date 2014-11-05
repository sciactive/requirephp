<?php
/**
 * RequirePHP
 *
 * An implementation of dependency injection and service location (like
 * RequireJS) in PHP. Written by Hunter Perrin for 2be.io.
 *
 * @version 1.1.1
 * @license https://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://requirephp.org
 */

define('REQUIREPHP_MAX_DEPTH', 80);

class RPHP {
	private static $modules = array();
	private static $functions = array();
	private static $aliases = array();
	private static $depth = 0;

	public static function &_($arg1 = null, $arg2 = null, $arg3 = null) {
		if (isset($arg1) && !isset($arg2) && !isset($arg3)) { // Calling require('name') to get the object.
			$arg1 = RPHP::parseAlias($arg1);
			if (!RPHP::runModule($arg1))
				throw new RequireModuleFailedException("Can't load module $arg1.");
			return RPHP::$modules[$arg1]['return'];
		} elseif (!isset($arg3) && is_array($arg1) && is_callable($arg2)) { // Calling require(['dependency'], function(){}) to run a function when dependencies are met.
			RPHP::$functions[] = array('requires' => $arg1, 'function' => $arg2);
		} elseif (is_string($arg1) && is_array($arg2) && is_callable($arg3)) { // Calling require('name', ['dependency'], function(){}) to declare a named module.
			RPHP::$modules[$arg1] = array('requires' => $arg2, 'function' => $arg3);
		}
		RPHP::runFunctions();
	}

	public static function remove($name) {
		$name = "$name";
		if (empty($name))
			return;
		unset(RPHP::$modules[$name]);
	}

	public static function __callStatic($name, $arguments) {
		return call_user_func_array(array(RPHP, '_'), $arguments);
	}

	public function __call($name, $arguments) {
		return call_user_func_array(array(RPHP, '_'), $arguments);
	}

	public static function alias($name, $target) {
		$name = "$name";
		$target = "$target";
		if (empty($name) || empty($target))
			return;
		RPHP::$aliases[$name] = $target;
	}

	public static function removeAlias($name) {
		$name = "$name";
		if (empty($name))
			return;
		unset(RPHP::$aliases[$name]);
	}

	private static function parseAlias($name) {
		if (isset(RPHP::$modules[$name]))
			return $name;
		if (isset(RPHP::$aliases[$name]))
			return RPHP::parseAlias(RPHP::$aliases[$name]);
		return $name;
	}

	private static function runModule($name) {
		$name = RPHP::parseAlias($name);
		if (!isset(RPHP::$modules[$name]))
			return false;
		if (key_exists('return', RPHP::$modules[$name])) // If we've already loaded this module, we're golden.
			return true;
		RPHP::$depth++; // Keep track of how deep we're going.
		if (RPHP::$depth > REQUIREPHP_MAX_DEPTH) {
			RPHP::$depth = 0;
			throw new RequireTooDeepException("Proceeded too deeply down the rabbit hole. Max require depth is ".REQUIREPHP_MAX_DEPTH.".");
		}
		// Load the required modules.
		$arguments = array();
		if (!empty(RPHP::$modules[$name]['requires'])) {
			foreach (RPHP::$modules[$name]['requires'] as $require) {
				$require = RPHP::parseAlias($require);
				if (!RPHP::runModule($require)) {
					RPHP::$depth--;
					return false;
				}
				$arguments[] = RPHP::$modules[$require]['return']; // Add this return value to the arguments. We'll pass it to the callback.
			}
		}
		if (is_callable(RPHP::$modules[$name]['function'])) {
			RPHP::$modules[$name]['return'] = call_user_func_array(RPHP::$modules[$name]['function'], $arguments);
		} else {
			RPHP::$depth--;
			return false;
		}
		RPHP::$depth--;
		return true;
	}

	private static function runFunctions() {
		foreach (RPHP::$functions as $key => $function) {
			// Load the required modules.
			$arguments = array();
			foreach ($function['requires'] as $require) {
				$require = RPHP::parseAlias($require);
				if (!RPHP::runModule($require))
					continue 2;
				$arguments[] = RPHP::$modules[$require]['return']; // Add this return value to the arguments. We'll pass it to the callback.
			}
			call_user_func_array($function['function'], $arguments);
			unset(RPHP::$functions[$key]);
		}
	}
}

class RequireTooDeepException extends Exception {}
class RequireModuleFailedException extends Exception {}

class_alias('RPHP', 'RequirePHP');
