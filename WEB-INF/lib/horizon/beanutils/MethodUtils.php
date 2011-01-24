<?php
/* $Id: MethodUtils.php 370 2006-10-17 05:19:38Z mojavelinux $
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
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.beanutils
 */
class MethodUtils extends Object
{
	/**
	 * Construct the reader method for this bean based on the name of
	 * the property.  If the getter method cannot be found, the property
	 * is assumed to be a boolean and the is method is used.
	 *
	 * @param Object $bean The subject bean
	 * @param string $property The name of the bean property
	 */
	function getReadMethod(&$bean, $property)
	{
		$method = 'get' . ucfirst($property);
		if (!method_exists($bean, $method))
		{
			$method = 'is' . ucfirst($property);	
		}

		if (!method_exists($bean, $method))
		{
			$method = null;
		}

		return $method;
	}

	function getWriteMethod(&$bean, $property)
	{
		$method = 'set' . ucfirst($property);
		return method_exists($bean, $method) ? $method : null;
	}

	function isReadable(&$bean, $name)
	{
		return !is_null(MethodUtils::getReadMethod($bean, $name));
	}

	function isWriteable(&$bean, $name)
	{
		return !is_null(MethodUtils::getWriteMethod($bean, $name));
	}

	/**
	 * Retrieve methods that are not bean methods, which means they
	 * are not getters or setters
	 */
	function getNonBeanMethods(&$bean)
	{
		$allMethods = get_class_methods($bean);
		$nonBeanMethodFilter = create_function('$var', 'return (strpos($var, \'get\') === 0 || strpos($var, \'is\') === 0 || strpos($var, \'set\') === 0) ? false : true;');
		$nonBeanMethods = array_filter($allMethods, $nonBeanMethodFilter);
		return $nonBeanMethods;
	}

	/**
	 * @throws IllegalArgumentException, NoSuchMethodException
	 */
	function &invokeMethod(&$bean, $methodName, $args = array())
	{
		if (!method_exists($bean, $methodName))
		{
			throw_exception(new NoSuchMethodException('No method ' . $methodName . '() on class ' . get_class($bean)));
			return;
		}

		// FIXME: catch an invocation exception here
		switch (count($args))
		{
			case 0:
				$return =& ref($bean->$methodName());
			break;

			case 1:
				$return =& ref($bean->$methodName($args[0]));
			break;

			default:
				$return =& ref(call_user_func_array(array(&$bean, $methodName), $args));
		}

		return $return;
	}
}
?>
