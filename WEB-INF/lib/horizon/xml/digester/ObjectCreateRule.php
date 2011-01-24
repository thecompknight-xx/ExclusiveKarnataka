<?php
/* $Id: ObjectCreateRule.php 370 2006-10-17 05:19:38Z mojavelinux $
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

/**
 * Rule implementation that creates a new object and pushes it
 * onto the object stack.  When the element is complete, the
 * object will be popped.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan, Scott Sanders
 * @package horizon.xml.digester
 */
class ObjectCreateRule extends Rule
{
	var $attributeName = null;

	var $className = null;

	function ObjectCreateRule($className, $attributeName = null)
	{
		$this->className = $className;
		$this->attributeName = $attributeName;
	}

	function begin($namespace, $name, $attributes)
	{
		$realClassName = $this->className;
		if (!is_null($this->attributeName))
		{
			$value = isset($attributes[$this->attributeName]) ? $attributes[$this->attributeName] : null;
			if (!is_null($value))
			{
				$realClassName = $value;
			}
		}

		$clazz =& Clazz::forName($realClassName);
		if (!is_null($clazz))
		{
			$instance =& $clazz->newInstance();
			$this->digester->push($instance);
		}
	}

	function end($namespace, $name)
	{
		$top =& $this->digester->pop();
	}
}
?>
