<?php
/* $Id: BeanPropertySetterRule.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.xml.digester.Rule');
import('horizon.beanutils.BeanUtils');
import('horizon.beanutils.MethodUtils');

/**
 * @package horizon.xml.digester
 * @author Dan Allen
 */
class BeanPropertySetterRule extends Rule
{
	/**
	 * Set this property on the top object
	 */
	var $propertyName = null;

	/**
	 * The body text used to set the property
	 */
	var $bodyText = null;

	function BeanPropertySetterRule($propertyName = null)
	{
		$this->propertyName = $propertyName;
	}

	/**
	 * Process the body of this element, which contains
	 * the value used to set the bean property on the object
	 */
	function body($namespace, $name, $text)
	{
		$this->bodyText = trim($text);
	}

	/**
	 * Process the end of the element, which is where the
	 * actually method call on the bean will occur.
	 */
	function end($namespace, $name)
	{
		$property = $this->propertyName;	

		// a property name was not provided, so assume the XML tag name is
		// in fact the name of the property on the bean
		if (is_null($property))
		{
			$property = $name;	
		}

		// get a reference to the top object
		$top =& $this->digester->peek();

		// set the property on the top object, throwing an exception
		// if the property cannot be found
		if (!MethodUtils::isWriteable($top, $property))
		{
			throw_exception(new NoSuchMethodException('Bean has no property named \'' . $property . '\''));
			return;
		}

		// set the property, with conversion as necessary
		BeanUtils::setProperty($top, $property, $this->bodyText);
	}

	/**
	 * Clean up after parsing is complete.
	 */
	function finish()
	{
		$this->bodyText = null;
	}
}
?>
