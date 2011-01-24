<?php
/* $Id: init.php 370 2006-10-17 05:19:38Z mojavelinux $
 *
 * Copyright 2003-2005 Dan Allen, Mojavelinux.com (dan.allen@mojavelinux.com)
 *
 * This project was originally created by Dan Allen, but you are permitted to
 * use it, modify it and/or contribute to it.  It has been largely inspired by
 * a handful of other open source projects and public specifications, most
 * notably Apache's Jakarta Project and Sun Microsystem's J2EE SDK.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @package horizon
 * @packagetutorial package.html
 */

import('horizon.lang.Object');
import('horizon.lang.RootException');
import('horizon.lang.Clazz');
import('horizon.lang.String');
import('horizon.lang.IllegalArgumentException');
import('horizon.lang.IllegalStateException');
import('horizon.lang.NoSuchMethodException');
import('horizon.lang.NullPointerException');
import('horizon.lang.SecurityException');
import('horizon.lang.ClassNotFoundException');

// clear out legacy global variables which includes
// HTTP_* and the argc/argv (which are still available under _SERVER)
// this leaves behind _GET/_POST/_COOKIE/_SERVER/_SESSION/_ENV/_FILES
unset($GLOBALS['argv']);
unset($GLOBALS['argc']);
unset($GLOBALS['HTTP_POST_VARS']);
unset($GLOBALS['HTTP_GET_VARS']);
unset($GLOBALS['HTTP_COOKIE_VARS']);
unset($GLOBALS['HTTP_SERVER_VARS']);
unset($GLOBALS['HTTP_ENV_VARS']);
unset($GLOBALS['HTTP_POST_FILES']);

define('FATAL_ERROR_PREFIX', 'Fatal error: ');
set_error_handler('handle_exception');
// NOTE: it is necessary to handle array to solve some PHP reference problems
$_EXCEPTION = array();
// NOTE: we use a constants array to provide naming flexibility
$_CONSTANTS = array();
// NOTE: at certain points, the umask is unreliable, so let's track it manually
$_UMASK = umask();

/**
 * Assign the value of a constant, which internally is stored in the global
 * constant array.
 */
function def($name, $value)
{
	global $_CONSTANTS;
	$_CONSTANTS[$name] = $value;
}

/**
 * Get the value of a constant...a short little helper function that
 * will throw an exception if it doesn't exist.
 *
 * The convention for defining class constants is to use ClassName::CONSTANT_NAME
 * in a case-sensitive mechanism.  This will allow for each conversion to the PHP5
 * runtime environment since it follows the syntax used by zend2.  However, in PHP4,
 * it is necessary to quote the constant when defining it since :: is a reserved symbol
 * and will throw a parse error.  The c() method will allow for php4/5 compatibility.
 */
function c($name)
{
	global $_CONSTANTS;
	if (!isset($_CONSTANTS[$name]))
	{
		// the equivalent of a parse error, since this should be caught before the fact
		trigger_error('cannot resolve symbol: constant ' . $name, E_USER_ERROR);
	}

	return $_CONSTANTS[$name];
}

function throw_exception(&$e)
{
	global $_EXCEPTION;
	
	// only save the exception if one does not already exist
	// NOTE: it is necessary to use an array for purposes of reference bugs in PHP
	if (count($_EXCEPTION) == 0)
	{
		$_EXCEPTION[0] =& $e;
	}
}

function handle_exception($level, $message, $file, $line)
{
	static $exceptionMatches = array(

		'missing argument' => 'IllegalArgumentException',

		'call to undefined function' => 'NoSuchMethodException',

		'call to a member function on a non-object' => 'NullPointerException',

	);

	// ignore if manually silenced with '@' or E_STRICT (PHP5)
	if (error_reporting() == 0 || $level == E_STRICT)
	{
		return;
	}
	else
	{
		foreach ($exceptionMatches as $match => $className)
		{
			if (stristr($message, $match))
			{
				// file and line? we don't need with debug_backtrace
				throw_exception(new $className($message));
				return;
			}
		}

		// file and line? we don't need with debug_backtrace
		throw_exception(new RootException($message));
	}
}

/**
 * Simulate an exception catch by looking for the exception type
 * in the globally stored $_EXCEPTION variable.
 *
 * @return {@link RootException} The active exception or <kbd>null</kbd> if an
 * exception of the requested type is not active.
 */
function catch_exception($clazz = 'RootException')
{
	global $_EXCEPTION;

	$instance = null;
	$clazz = strtolower($clazz);
	if (!empty($_EXCEPTION) && is_a($_EXCEPTION[0], $clazz))
	{
		$instance = array_pop($_EXCEPTION);
	}

	return $instance;
}

/**
 * Return a boolean depending on whether an exception is bubbling
 *
 * By running <code>if (bubble_exception()) return;</code> we can allow
 * an existing exception to force the current block to stop executing and
 * return to the caller.  Unfortunately, this must be explicitly added to
 * the code in spots where code execution should not continue in the case
 * that an exception has been thrown.
 */
function bubble_exception()
{
	global $_EXCEPTION;
	if (empty($_EXCEPTION)) {
		return false;
	}

	// attempt to log in the event that it doesn't propagate
	error_log('Exception detected: ' . $_EXCEPTION[0]->getStackTrace("\n"), 0);	
	return true;
}

/**
 * Return the actual paths that have been included.
 *
 * If type is specified as 1, only class includes are returned and if type is
 * 2, only non-class includes are returned.  Any other type will return all the
 * includes.
 *
 * @param int $type Optional type
 */
function get_included_paths($type = 0)
{
	// get all include files
	if ($type == 0)
	{
		return get_included_files();
	}
	// get only class includes
	elseif ($type == 1)
	{
		$paths = array();

		foreach (get_included_files() as $path)
		{
			$file = basename($path);	
			// only consider files that begin with an upper case letter
			if (strtoupper($file[0]) == $file[0])
			{
				$paths[] = $path;
			}
		}

		return $paths;
	}
	// get non-class includes
	elseif ($type == 2)
	{
		$paths = array();

		foreach (get_included_files() as $path)
		{
			$file = basename($path);	
			if (strtolower($file[0]) == $file[0])
			{
				$paths[] = $path;
			}
		}

		return $paths;
	}
}

/**
 * Return the include paths (or as java calls them resources directories) as an
 * array, each path represented as an absolute file on the system.
 *
 * @return array
 */
function get_include_paths()
{
	return array_map('realpath', explode(PATH_SEPARATOR, get_include_path()));
}

/**
 * Import a class using the java naming syntax for a class name.
 *
 * For instance, the package <i>horizon.io.FileReader</i> would
 * be equivalent to requiring the file <i>horizon/io/FileReader.php</i>
 * which would search the include path.
 *
 * TODO: protect against the same class name in a different package by providing a warning!!
 *
 * @param string $name The name of the package to be imported
 * @return void
 */
function import($name)
{
	include_once str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php';
}

/**
 * Get the current unix time in milliseconds
 */
function gettimemillis()
{
	list($usec, $sec) = explode(' ', microtime()); 
	return round((1000 * $sec) + (1000 * $usec));
}

/**
 * Determine if the specified value is 'empty' in terms of having contents,
 * not in terms of its value, as is the default case in php
 *
 * @param mixed $value
 *
 * @return boolean
 */
function is_empty($value)
{
	if (is_null($value) ||
	   (is_string($value) && strlen($value) == 0) ||
	   (is_array($value) && count($value) == 0))
	{
		return true;
	}

	return false;
}

/**
 * An identity function which will return the value
 * passed in as a reference.  This is a workaround used
 * when parameters are required to be passed by reference
 * and the value being passed in is either a literal or
 * a function call that does not return a reference.
 *
 * @param mixed $value
 *
 * @return mixed
 */
function &ref($value)
{
	return $value;
}

// Create functions which did not exist prior to PHP 5
if (strcmp(phpversion(), '5.0.0') < 0)
{
	define('E_STRICT', 2048);
}

// Create functions which did not exist prior to PHP 4.3
if (strcmp(phpversion(), '4.3.0') < 0)
{
	// assume PATH_SEPARATOR follows DIRECTORY_SEPARATOR
	define('PATH_SEPARATOR', DIRECTORY_SEPARATOR == '/' ? ':' : ';');

	function html_entity_decode($string)
	{
		$string = str_replace('&gt;', '>', $string);
		$string = str_replace('&lt;', '<', $string);
		$string = str_replace('&quot;', '"', $string);
		$string = str_replace('&amp;', '&', $string);
		return $string;
	}

	function apache_request_headers()
	{
		return getallheaders();
	}

	function get_include_path()
	{
		return ini_get('include_path');
	}

	function file_get_contents($filename)
	{
		$fd = fopen($filename, 'rb');
		$contents = fread($fd, filesize($filename));
		fclose($fd);
		return $contents;
	}

	function ob_get_clean()
	{
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
}

// Create functions which did not exist prior to PHP 4.2
if (strcmp(phpversion(), '4.2.0') < 0)
{
	function is_a(&$object, $className)
	{
		$className = strtolower($className);
		return ($className == strtolower(get_class($object)) || is_subclass_of($object, $className));
	}
}
?>
