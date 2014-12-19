<?php namespace SciActive;
/**
 * RequirePHP
 *
 * An implementation of dependency injection and service location (like
 * RequireJS) in PHP. Written by Hunter Perrin for SciActive.
 *
 * @version 1.2.0
 * @license https://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://requirephp.org
 */

if (!defined('REQUIREPHP_MAX_DEPTH')) {
	define('REQUIREPHP_MAX_DEPTH', 80);
}

if (!class_exists('RequireModuleFailedException')) {
	require_once __DIR__.DIRECTORY_SEPARATOR.'RequireModuleFailedException.php';
}

if (!class_exists('RequireTooDeepException')) {
	require_once __DIR__.DIRECTORY_SEPARATOR.'RequireTooDeepException.php';
}

class R {
	private static $modules = array();
	private static $functions = array();
	private static $aliases = array();
	private static $depth = 0;

	public static function _($arg1 = null, $arg2 = null, $arg3 = null) {
		if (isset($arg1) && !isset($arg2) && !isset($arg3)) { // Calling require('name') to get the object.
			$arg1 = R::parseAlias($arg1);
			if (!R::runModule($arg1)) {
				throw new RequireModuleFailedException("Can't load module $arg1.");
			}
			return R::$modules[$arg1]['return'];
		} elseif (!isset($arg3) && is_array($arg1) && is_callable($arg2)) { // Calling require(['dependency'], function(){}) to run a function when dependencies are met.
			R::$functions[] = array('requires' => $arg1, 'function' => $arg2);
		} elseif (is_string($arg1) && is_array($arg2) && is_callable($arg3)) { // Calling require('name', ['dependency'], function(){}) to declare a named module.
			R::$modules[$arg1] = array('requires' => $arg2, 'function' => $arg3);
		}
		R::runFunctions();
	}

	public static function undef($name) {
		$name = "$name";
		if (empty($name)) {
			return;
		}
		unset(R::$modules[$name]);
	}

	public static function __callStatic($name, $arguments) {
		return call_user_func_array(array(R, '_'), $arguments);
	}

	public function __call($name, $arguments) {
		return call_user_func_array(array(R, '_'), $arguments);
	}

	public static function alias($name, $target) {
		$name = "$name";
		$target = "$target";
		if (empty($name) || empty($target)) {
			return;
		}
		R::$aliases[$name] = $target;
	}

	public static function undefAlias($name) {
		$name = "$name";
		if (empty($name)) {
			return;
		}
		unset(R::$aliases[$name]);
	}

	private static function parseAlias($name) {
		if (isset(R::$modules[$name])) {
			return $name;
		}
		if (isset(R::$aliases[$name])) {
			return R::parseAlias(R::$aliases[$name]);
		}
		return $name;
	}

	private static function runModule($name) {
		$name = R::parseAlias($name);
		if (!isset(R::$modules[$name])) {
			return false;
		}
		if (key_exists('return', R::$modules[$name])) { // If we've already loaded this module, we're golden.
			return true;
		}
		R::$depth++; // Keep track of how deep we're going.
		if (R::$depth > REQUIREPHP_MAX_DEPTH) {
			R::$depth = 0;
			throw new RequireTooDeepException("Proceeded too deeply down the rabbit hole. Max require depth is ".REQUIREPHP_MAX_DEPTH.".");
		}
		// Load the required modules.
		$arguments = array();
		if (!empty(R::$modules[$name]['requires'])) {
			foreach (R::$modules[$name]['requires'] as $require) {
				$require = R::parseAlias($require);
				if (!R::runModule($require)) {
					R::$depth--;
					return false;
				}
				$arguments[] = R::$modules[$require]['return']; // Add this return value to the arguments. We'll pass it to the callback.
			}
		}
		if (is_callable(R::$modules[$name]['function'])) {
			R::$modules[$name]['return'] = call_user_func_array(R::$modules[$name]['function'], $arguments);
		} else {
			R::$depth--;
			return false;
		}
		R::$depth--;
		return true;
	}

	private static function runFunctions() {
		foreach (R::$functions as $key => $function) {
			// Load the required modules.
			$arguments = array();
			foreach ($function['requires'] as $require) {
				$require = R::parseAlias($require);
				if (!R::runModule($require)) {
					continue 2;
				}
				$arguments[] = R::$modules[$require]['return']; // Add this return value to the arguments. We'll pass it to the callback.
			}
			call_user_func_array($function['function'], $arguments);
			unset(R::$functions[$key]);
		}
	}
}
