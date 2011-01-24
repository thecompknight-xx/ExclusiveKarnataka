<?php
/* $Id: BeanUtils.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('horizon.beanutils.ConvertUtils');
import('horizon.beanutils.MethodUtils');
import('horizon.beanutils.PropertyUtils');

/**
 * Utility methods for getting/setting properties on
 * php variable types.
 *
 * In the case of arrays, keys are
 * references, whereas with objects the getter/setter methods
 * are used.
 *
 * <i>Based on java reflection techniques used in org.apache.commons.beanutils</i>
 *
 * IMPLEMENTATION NOTE: It is very critical to pay attention to references here.  Resolving
 * nested objects requires that no accidental cross-references be established that will modify
 * the variable which is passed in as the initial bean.  There is no easy way to do this in
 * PHP except to maintain two variables.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig R. McClanahan, Ralph Schaer, Chris Audley, Rey Francois, Gregor Raman
 * @package horizon.beanutils
 * @access public
 */
class BeanUtils extends Object
{
    /**
     * Clone a bean based on the available property getters and setters
     *
	 * @param Object $bean Bean object to be cloned
	 * @return Object
	 */
	function &cloneBean(&$bean)
	{
		if (!is_a($bean, 'Object'))
		{
			// QUESTION: throw error or return null?
			return ref(null);
		}

		$clazz =& $bean->getClass();
		$newBean =& $clazz->newInstance();
		BeanUtils::copyProperties($newBean, $bean);
		return $newBean;
	}

	function copyProperties(&$dest, &$orig)
	{
		$properties = BeanUtils::describe($orig);
		BeanUtils::populate($dest, $properties);
	}

	function copyProperty(&$bean, $name, &$value)
	{
	}

	/**
	 * Return a map of properties and values that can be used to populate
	 * another bean.
	 *
	 * NOTE: the methods inherited from Object are filter, which include
	 * getClass(), getClassName() and getPhpClassName()
	 * NOTE: no reference here since it is acceptible to return copy of map
	 */
	function describe(&$bean)
	{
		$allMethods = get_class_methods($bean);
		$properties = array();
		foreach ($allMethods as $method)
		{
			if (strpos($method, 'get') === 0)
			{
				$name = strtolower(substr($method, 3));
				// FIXME: abstract this filter!
				// skip methods inherited from Object
				if ($name == 'class' || $name == 'classname' || $name == 'phpclassname')
				{
					continue;
				}

				$properties[$name] = PropertyUtils::getSimpleProperty($bean, $name);
			}
			else if (strpos($method, 'is') === 0)
			{
				$name = strtolower(substr($method, 2));
				$properties[$name] =& PropertyUtils::getSimpleProperty($bean, $name);
			}
		}

		return $properties;
	}

	/**
	 * @return string
	 */
	function getMappedProperty(&$bean, $name, $key = null)
	{
		$value =& PropertyUtils::getMappedProperty($bean, $name, $key);
		$value = ConvertUtils::convert($value, 'string');
		return $value;
	}

	/**
	 * @return string
	 */
	function getIndexedProperty(&$bean, $name, $index = null)
	{
		$value =& PropertyUtils::getIndexedProperty($bean, $name, $index);
		$value = ConvertUtils::convert($value, 'string');
		return $value;
	}

	/**
	 * @return string
	 */
	function getNestedProperty(&$bean, $name)
	{
		$value =& PropertyUtils::getNestedProperty($bean, $name);
		$value = ConvertUtils::convert($value, 'string');
		return $value;
	}

	/**
	 * @return string
	 */
	function &getProperty(&$bean, $name)
	{
		return BeanUtils::getNestedProperty($bean, $name);
	}

	/**
	 * @return string
	 */
	function getSimpleProperty(&$bean, $name)
	{
		$value =& PropertyUtils::getSimpleProperty($bean, $name);
		$value = ConvertUtils::convert($value, 'string');
		return $value;
	}

	/**
	 * Populate the properties of the specified bean, based on
     * the specified name/value pairs.  This method uses reflection
     * to identify corresponding "property setter" method names.
     * This method is specifically designed for extracting string
	 * parameters from an HTTP Request.  For general property copying
	 * use the {@link copyProperties()} method instead.
	 *
	 * @return void
     */
	function populate(&$bean, $properties)
	{
		// do nothing unless both arguments have been specified
		if (is_null($bean) || is_null($properties))
		{
			return;
		}

		foreach (array_keys($properties) as $name)
		{
			if (is_null($name))
			{
				continue;
			}

			// perform assignment for this property
			BeanUtils::setProperty($bean, $name, $properties[$name]);
		}
	}

	function setProperty(&$bean, $name, &$value)
	{
		$target = null;
		$name =& new String($name);
		$delim = $name->lastIndexOf('.');
		if ($delim >= 0)
		{
			$resolveName =& $name->substring(0, $delim);
			$resolveName = $resolveName->toString();
			// try {
			$target =& PropertyUtils::getProperty($bean, $resolveName);
			// } catch (NoSuchMethodException $e) {
			if ($e = catch_exception('NoSuchMethodException'))
			{
				return; // skip the setter
			}
			// }

			$name =& $name->substring($delim + 1);
		}

		$propName =& $name;
		$key = null;

		$j = $propName->indexOf('(');
		if ($j >= 0)
		{
			$k = $propName->indexOf(')');
			$key =& $propName->substring($j + 1, $k);
			$key = $key->toString();
			$propName =& $propName->substring(0, $j);
		}

		$propName = $propName->toString();

		if (!is_null($target))
		{
			$writeMethod = MethodUtils::getWriteMethod($target, $propName);
			if (is_null($writeMethod))
			{
				return; // skip, no write method
			}

			MethodUtils::invokeMethod($target, $writeMethod, array(&$value));
		}
		else
		{
			$writeMethod = MethodUtils::getWriteMethod($bean, $propName);
			if (is_null($writeMethod))
			{
				return; // skip, no write method
			}

			MethodUtils::invokeMethod($bean, $writeMethod, array(&$value));
		}
	}
}
?>
