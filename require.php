<?php
/**
 * RequirePHP
 *
 * An implementation of dependency injection and service location (like
 * RequireJS) in PHP. Written by Hunter Perrin for 2be.io.
 *
 * @version 1.1.0
 * @license https://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://requirephp.org
 */

define('REQUIREPHP_MAX_DEPTH', 80);

class RequirePHP {
	private static $modules = array();
	private static $functions = array();
	private static $aliases = array();
	private static $depth = 0;

	public static function &_($arg1 = null, $arg2 = null, $arg3 = null) {
		if (isset($arg1) && !isset($arg2) && !isset($arg3)) { // Calling require('name') to get the object.
			$arg1 = RequirePHP::parseAlias($arg1);
			if (!RequirePHP::runModule($arg1))
				throw new RequireModuleFailedException("Can't load module $arg1.");
			return RequirePHP::$modules[$arg1]['return'];
		} elseif (!isset($arg3) && is_array($arg1) && is_callable($arg2)) { // Calling require(['dependency'], function(){}) to run a function when dependencies are met.
			RequirePHP::$functions[] = array('requires' => $arg1, 'function' => $arg2);
		} elseif (is_string($arg1) && is_array($arg2) && is_callable($arg3)) { // Calling require('name', ['dependency'], function(){}) to declare a named module.
			RequirePHP::$modules[$arg1] = array('requires' => $arg2, 'function' => $arg3);
		}
		RequirePHP::runFunctions();
		return RequirePHP;
	}

	public static function __callStatic($name, $arguments) {
		return call_user_func_array(array(RequirePHP, '_'), $arguments);
	}

	public function __call($name, $arguments) {
		return call_user_func_array(array(RequirePHP, '_'), $arguments);
	}

	public static function alias($name, $target) {
		$name = "$name";
		$target = "$target";
		if (empty($name) || empty($target))
			return false;
		RequirePHP::$aliases[$name] = $target;
		return RequirePHP;
	}

	private static function parseAlias($name) {
		if (isset(RequirePHP::$modules[$name]))
			return $name;
		if (isset(RequirePHP::$aliases[$name]))
			return RequirePHP::parseAlias(RequirePHP::$aliases[$name]);
		return $name;
	}

	private static function runModule($name) {
		$name = RequirePHP::parseAlias($name);
		if (!isset(RequirePHP::$modules[$name]))
			return false;
		if (key_exists('return', RequirePHP::$modules[$name])) // If we've already loaded this module, we're golden.
			return true;
		RequirePHP::$depth++; // Keep track of how deep we're going.
		if (RequirePHP::$depth > REQUIREPHP_MAX_DEPTH) {
			RequirePHP::$depth--;
			throw new RequireTooDeepException("Proceeded too deeply down the rabbit hole. Max require depth is ".REQUIREPHP_MAX_DEPTH.".");
		}
		// Load the required modules.
		$arguments = array();
		if (!empty(RequirePHP::$modules[$name]['requires'])) {
			foreach (RequirePHP::$modules[$name]['requires'] as $require) {
				$require = RequirePHP::parseAlias($require);
				if (!RequirePHP::runModule($require)) {
					RequirePHP::$depth--;
					return false;
				}
				$arguments[] = RequirePHP::$modules[$require]['return']; // Add this return value to the arguments. We'll pass it to the callback.
			}
		}
		if (is_callable(RequirePHP::$modules[$name]['function'])) {
			RequirePHP::$modules[$name]['return'] = call_user_func_array(RequirePHP::$modules[$name]['function'], $arguments);
		} else {
			RequirePHP::$depth--;
			return false;
		}
		RequirePHP::$depth--;
		return true;
	}

	private static function runFunctions() {
		foreach (RequirePHP::$functions as $key => $function) {
			// Load the required modules.
			$arguments = array();
			foreach ($function['requires'] as $require) {
				$require = RequirePHP::parseAlias($require);
				if (!RequirePHP::runModule($require))
					continue 2;
				$arguments[] = RequirePHP::$modules[$require]['return']; // Add this return value to the arguments. We'll pass it to the callback.
			}
			call_user_func_array($function['function'], $arguments);
			unset(RequirePHP::$functions[$key]);
		}
	}
}

class RequireTooDeepException extends Exception {}
class RequireModuleFailedException extends Exception {}

class_alias('RequirePHP', 'RPHP');