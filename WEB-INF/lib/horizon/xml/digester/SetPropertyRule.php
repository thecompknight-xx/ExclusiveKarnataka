<?php
/* $Id: SetPropertyRule.php 188 2005-04-07 04:52:31Z mojavelinux $
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

/**
 * Rule implementation that sets an individual property on the object at the
 * top of the stack, based on attributes with specified names.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan
 * @package horizon.xml.digester
 */
class SetPropertyRule extends Rule
{
	var $name = null;

	var $value = null;

	/**
	 * Construct a "set property" rule with the specified name and value attributes.
	 * @param string $name name of the attribute that contains the property name
	 * @param string $value name of the attribute that contains the property value
	 */
	function SetPropertyRule($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * Process the beginning of this element.
	 * @param string $namespace namespace URI of matching element or empty
	 *   string if parser not namespace aware or element has no namespace
	 * @param string $name local name if parser is namespace aware or just element name
	 * @param array $attributes The attribute list of this element.
	 */
	function begin($namespace, $name, $attributes)
	{
		$actualName = null;
		$actualValue = null;

		foreach ($attributes as $attrName => $value)
		{
			if ($attrName == $this->name)
			{
				$actualName = $value;
			}
			elseif ($attrName == $this->value)
			{
				$actualValue = $value;
			}
		}
		
		$top =& $this->digester->peek();
		
		BeanUtils::setProperty($top, $actualName, $actualValue);
	}
}
?>
