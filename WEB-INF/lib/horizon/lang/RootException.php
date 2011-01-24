<?php
/* $Id: RootException.php 347 2006-05-15 03:40:31Z mojavelinux $
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

import('horizon.util.StringUtils');

/**
 * The <strong>RootException</strong> is the top level exception which
 * can either be used for general exceptions or can be subclassed to
 * create a more specific exception.  Because of the way exceptions must
 * be implemented in PHP (prior to PHP5), these exceptions are runtime
 * exceptions and must be handled explicitly.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.lang
 * @access public
 */
class RootException extends Object
{
	/**
	 * The message stored when the exception was thrown
	 * @var string
	 */
	var $message;

	/**
	 * The cause stored when the exception was thrown
	 * @var RootException
	 */
	var $cause;

	/**
	 * An array of the backtrace information available at the
	 * time when the exception was thrown
	 * @var array
	 */
	var $stacktrace;

	/**
	 * Constructs a new Throwable with the specified error message. 
	 * Also, the method fillInStackTrace() is called for this object. 
	 *
	 * @param string $message Optional message associated with the exception
	 * @param RootException $cause Optional cause associated with the exception
	 * TODO: we lose the reference for the cause when we throw the exception
	 */
	function RootException($message = null, $cause = null)
	{
		$this->message = $message;
		$this->fillInStackTrace();
		$this->cause = $cause;
	}

	/**
	 * Returns the error message string of this throwable object.
	 *
	 * @return string
	 */
	function getMessage()
	{
		return $this->message;
	}

	/**
	 * Returns the cause of this throwable object, or null if no cause exists
	 *
	 * @return RootException
	 */
	function &getCause()
	{
		return $this->cause;
	}

	/**
	 * Returns a short description of this exception.
	 * If this RootException object was created with an error message string, then
	 * the result is the concatenation of three strings:
     *  o The name of the actual class of this object
     *  o ": " (a colon and a space)
     *  o The result of the getMessage() method for this object 
	 * If this RootException object was created with no error message string, then
	 * the name of the actual class of this object is returned.
	 *
	 * @return string
	 */
	function toString()
	{
		$msg = Clazz::getQualifiedName($this);
		if (!is_null($this->message))
		{
			$msg .= ': ' . $this->message;
		}

		return $msg;
	}

	/**
	 * Prints this Throwable and its backtrace to the standard output.
	 * The first line of output contains the result of the toString() method
	 * for this object. Remaining lines represent data previously recorded by
	 * the method fillInStackTrace().
	 *
	 * @return void
	 */
	function printStackTrace($eol = null)
	{
		echo $this->getStackTrace($eol);
	}

	/**
	 * Prints this Throwable and its backtrace to the standard output.
	 * The first line of output contains the result of the toString() method
	 * for this object. Remaining lines represent data previously recorded by
	 * the method fillInStackTrace().
	 *
	 * @return string
	 */
	function getStackTrace($eol = null)
	{
		if (is_null($eol)) {
			$interface = php_sapi_name();
			// COULD YOU PLEASE SETTLE ON THIS PHP DEVS!?!
			$eol = $interface == 'cli' || $interface == 'cgi' ? "\n" : '<br />';
		}

		$out = $this->toString() . $eol;

		// @note each time we look back to previous point because we want to
		// know what line and what file the method was called from
		for ($i = 1; $i < count($this->stacktrace); $i++)
		{
			$point = $this->stacktrace[$i];
			$prevpoint = $this->stacktrace[$i - 1];

			// if the class is not set, it is a PHP function
			if (!isset($point['class']))
			{
				$point['class'] = '[PHP]';
			}
			else
			{
				$point['class'] = Clazz::getQualifiedName($point['class']);
			}

			if (!isset($point['function']))
			{
				$point['function'] = '[PHP]';
			}

			$out .= "\tat " . $point['class'] . '.' . $point['function'] . '(' . StringUtils::substringAfterLast($prevpoint['file'], '/') . ':' . $prevpoint['line'] . ')' . $eol;
		}
		
		if (count($this->stacktrace))
		{
			$point = end($this->stacktrace);
			$out .= "\t" . 'at [PHP].main(' . StringUtils::substringAfterLast($point['file'], '/') . ':' . $point['line'] . ')' . $eol;
		}

		$cause = $this->getCause();
		if (!is_null($cause))
		{
			$out .= 'Caused by: ' . $cause->getStackTrace($eol);
		}

		return $out;
	}

	/**
	 * Fills in the execution stack trace. This method records within this
	 * RootException object information about the current state of the stack frames
	 * for the current thread.
	 *
	 * @return RootException this object
	 */
	function &fillInStackTrace()
	{
		if (function_exists('debug_backtrace'))
		{
			$stacktrace = debug_backtrace();
			// clear out the parts of the stacktrace spent in the exception classes (ending in 'exception')
			while (true)
			{
				if (empty($stacktrace))
				{
					break;
				}

				$point = $stacktrace[0];

				if ((isset($point['class']) && substr($point['class'], -9) == 'exception') ||
				     (isset($point['function']) && $point['function'] == 'handle_exception'))
				{
					array_shift($stacktrace);
					continue;
				}
				
				break;
			}

			$this->stacktrace = $stacktrace;
		}
		else
		{
			$this->stacktrace = array();
		}

		return $this;
	}
}
?>
