<?php
/* $Id: PropertyUtils.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('horizon.beanutils.MethodUtils');
import('horizon.beanutils.NestedBeanStack');

/**
 * Utility methods for using reflection to facilitate generic getter and setter
 * operations on Bean objects.
 *
 * Five general formats can be used for referencing, possibly nested,
 * property values of the specified bean, with the layout presented in
 * paranthesis.
 * <ul>
 * <li><b>Simple</b>: <var>name</var> - The specified
 *     <var>name</var> identifies an individual property of a particular
 *     Bean.</li>
 * <li><b>Nested</b>: <var>name1.name2.name3</var> - The first
 *     name element is used to select a property getter, as for simple
 *     references above.  The object returned for this property is then
 *     consulted, using the same approach, for a property getter for a
 *     property named <var>name2</var>, and so on.  The property value that
 *     is ultimately retrieved or modified is the one identified by the
 *     last name element.</li>
 * <li><b>Indexed</b>: <var>name[index]</var> - The underlying
 *     property value is assumed to be an array. The appropriate
 *     (zero-relative) entry in the array is selected.</li>
 * <li><b>Mapped</b>: <var>name(key)</var> - The Bean
 *     is assumed to have an property getter and setter methods with an
 *     additional attribute of type <var>string</var>.  Alternatively,
 *     the Bean can have a property which returns a hash, which is then consulted
 *     using the value of <var>key</var>.</li>
 * <li><b>Combined</b>: <var>name1.name2[index].name3(key)</var> -
 *     Combining mapped, nested, and indexed references is also
 *     supported.</li>
 * </ul>
 * 
 * @package horizon.beanutils
 * @author Dan Allen <dan.allen@mojavelinux.com><br />
 * <b>Credits:</b><br />
 *   Craig R. McClanahan<br />
 *   Ralph Schaer<br />
 *   Chris Audley<br />
 *   Rey Francois<br />
 *   Gregor Raman<br />
 *   Jan Sorensen<br />
 *   Scott Sanders<br />
 * TODO: still need setNestedProperty, setMappedProperty
 */
class PropertyUtils
{
	/**
	 * Get the value of the specified property on the subject bean using
	 * the standard notation for getter methods from the JavaBean spec.
	 *
	 * @return Object
	 */
	function &getSimpleProperty(&$bean, $name)
	{
		if (is_null($bean))
		{
			throw_exception(new IllegalArgumentException('Bean object cannot be null.'));
			return ref(null);
		}

		// TODO: make sure there are no instances of nested/mapped/indexed delimiters
		$readMethod = MethodUtils::getReadMethod($bean, $name);	
		if (is_null($readMethod))
		{
			throw_exception(new NoSuchMethodException('Unknown property ' . $name));
			return ref(null);
		}

		return MethodUtils::invokeMethod($bean, $readMethod, array());
	}

	function &getProperty(&$bean, $name)
	{
		return PropertyUtils::getNestedProperty($bean, $name);
	}

	/**
	 * NOTE: as long as we always assign a variable which is passed by
	 * reference to a reference, we don't actually reassign the value of
	 * the variable which we passed in.
	 */
	function &getNestedProperty(&$bean, $name)
	{
		$indexOfINDEXED_DELIM_BEGIN = -1;
		$indexOfMAPPED_DELIM_BEGIN = -1;
		$indexOfMAPPED_DELIM_END = -1;
		$indexOfNESTED_DELIM = -1;
		$name =& new String($name);

		$stack =& new NestedBeanStack();
		$stack->push($bean);
		$current =& $stack->getCurrent();

		while (true)
		{
			$indexOfNESTED_DELIM = $name->indexOf('.');
			$indexOfMAPPED_DELIM_BEGIN = $name->indexOf('(');
			$indexOfMAPPED_DELIM_END = $name->indexOf(')');

			if ($indexOfMAPPED_DELIM_END >= 0 && $indexOfMAPPED_DELIM_BEGIN >= 0 &&
			   ($indexOfNESTED_DELIM < 0 || $indexOfNESTED_DELIM > $indexOfMAPPED_DELIM_BEGIN))
			{
				$indexOfNESTED_DELIM = $name->indexOf('.', $indexOfMAPPED_DELIM_END);
			}
			else
			{
				$indexOfNESTED_DELIM = $name->indexOf('.');
			}

			if ($indexOfNESTED_DELIM < 0)
			{
				break;
			}

			$next =& $name->substring(0, $indexOfNESTED_DELIM);
			$indexOfINDEXED_DELIM_BEGIN = $next->indexOf('[');
			$indexOfMAPPED_DELIM_BEGIN = $next->indexOf('(');
			$next = $next->toString();
			// if our bean is a Map (or associative array)
			if (is_array($current))
			{
				if (isset($current[$next]))
				{
					$stack->push($current[$next]);
				}
				else
				{
					// @fixme: is this right?
					return ref(null);
				}
			}
			elseif ($indexOfMAPPED_DELIM_BEGIN >= 0)
			{
				$stack->push(PropertyUtils::getMappedProperty($current, $next));
			}
			elseif ($indexOfINDEXED_DELIM_BEGIN >= 0)
			{
				$stack->push(PropertyUtils::getIndexedProperty($current, $next));
			}
			else
			{
				$stack->push(PropertyUtils::getSimpleProperty($current, $next));
			}

			// @todo check for null value for $bean here and stop
			$name =& $name->substring($indexOfNESTED_DELIM + 1);
			$current =& $stack->getCurrent();
		}

		$indexOfINDEXED_DELIM_BEGIN = $name->indexOf('[');
		$indexOfMAPPED_DELIM_BEGIN = $name->indexOf('(');
		$name = $name->toString();

		// if our bean is a Map (or associative array)
		if (is_array($current))
		{
			if (isset($current[$name]))
			{
				$stack->push($current[$name]);
			}
			else
			{
				// fixme: is this right?
				return ref(null);
			}
		}
		elseif ($indexOfMAPPED_DELIM_BEGIN >= 0)
		{
			$stack->push(PropertyUtils::getMappedProperty($current, $name));
		}
		elseif ($indexOfINDEXED_DELIM_BEGIN >= 0)
		{
			$stack->push(PropertyUtils::getIndexedProperty($current, $name));
		}
		else
		{
			$stack->push(PropertyUtils::getSimpleProperty($current, $name));
		}

		return $stack->compact();
	}

	function &getIndexedProperty(&$bean, $name, $index = null)
	{
		$stack =& new NestedBeanStack();
		$stack->push($bean);
		$current =& $stack->getCurrent();

		if (is_null($index))
		{
			$name =& new String($name);
			$delim = $name->indexOf('[');
			$delim2 = $name->indexOf(']');
			if (($delim < 0 || $delim2 <= $delim))
			{
				throw_exception(new IllegalArgumentException('Invalid indexed property "' . $name->toString() . '"'));
				return ref(null);
			}

			$index = String::valueOf($name->substring($delim + 1, $delim2));
			$name = String::valueOf($name->substring(0, $delim));
		}

		$readMethod = MethodUtils::getReadMethod($bean, $name);

		if (is_null($readMethod))
		{
			throw_exception(new NoSuchMethodException('Unknown property ' . $name));
			return ref(null);
		}

		// try {
		$stack->push(MethodUtils::invokeMethod($current, $readMethod, array()));
		$current =& $stack->getCurrent();

		if (is_array($current))
		{
			// NOTE: don't condense array to eliminate gaps in the indices
			// since this can be destructive to the index we are going for...
			// assume that the bean method would have condensed appropriately
			//$stack->push(array_values($stack->pop()));
			$current =& $stack->getCurrent();
			if (isset($current[$index]))
			{
				$stack->push($current[$index]);
			}
			else
			{
				// NOTE: let's not throw an exception since null is a legitimate response
				//throw_exception(new NoSuchMethodException('No such index ' . $index . ' on property ' . $name));
				return ref(null);
			}
		}

		return $stack->compact();
	}

	function &getMappedProperty(&$bean, $name, $key = null)
	{
		$stack =& new NestedBeanStack();
		$stack->push($bean);
		$current =& $stack->getCurrent();

		if (is_null($key))
		{
			$name =& new String($name);
			$delim = $name->indexOf('(');
			$delim2 = $name->indexOf(')');
			if (($delim < 0 || $delim2 <= $delim))
			{
				throw_exception(new IllegalArgumentException('Invalid mapped property "' . $name->toString() . '"'));
				return ref(null);
			}

			$key = String::valueOf($name->substring($delim + 1, $delim2));
			$name = String::valueOf($name->substring(0, $delim));
		}

		$readMethod = MethodUtils::getReadMethod($current, $name);

		if (is_null($readMethod))
		{
			throw_exception(new NoSuchMethodException('Unknown property ' . $name));
			return ref(null);
		}

		// we will first try to call the method with no arguments, and if we get
		// back an array we know this was method returns a Map
		// if we get an illegalargumentexception then we know that we must pass
		// in our mapped key as a parameter

		// try {
		$stack->push(MethodUtils::invokeMethod($current, $readMethod, array()));

		$current =& $stack->getCurrent();

		if (is_array($current))
		{
			if (isset($current[$key]))
			{
				$stack->push($current[$key]);
			}
			else
			{
				throw_exception(new NoSuchMethodException('No such mapped key ' . $key . ' on property ' . $name));
			}
		}
		// } catch (IllegalArgumentException $e) {
		if ($e = catch_exception('IllegalArgumentException'))
		{
			$stack->push(MethodUtils::invokeMethod($current, $readMethod, array($key)));
		}
		// }

		return $stack->compact();
	}

	function setSimpleProperty(&$bean, $name, &$value)
	{
		$writeMethod = MethodUtils::getWriteMethod($bean, $name);
		if (is_null($writeMethod))
		{
			throw_exception(new NoSuchMethodException('Property "' . $name . '" has no setter method"'));
			return;
		}

		MethodUtils::invokeMethod($bean, $writeMethod, array(&$value));
	}
}
?>
