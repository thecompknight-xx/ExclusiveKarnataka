<?php
/* $Id: SetPropertiesRule.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * Rule implementation that sets properties on the object at the top of the
 * stack, based on attributes with corresponding names.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan, Scott Sanders
 * @package horizon.xml.digester
 */
class SetPropertiesRule extends Rule
{
	var $attributeNames = array();

	var $propertyNames = array();

	function SetPropertiesRule($attributeNames = array(), $propertyNames = array())
	{
		$this->attributeNames = $attributeNames;
		$this->propertyNames = $propertyNames;
	}

	function begin($namespace, $name, $attributes)
	{
		$numAttributeNames = count($this->attributeNames);
		$numPropertyNames = count($this->propertyNames);
		$values = array();

		foreach ($attributes as $attrName => $attrValue)
		{
			$actualName = $attrName;

			for ($i = 0; $i < $numAttributeNames; $i++)
			{
				if ($attrName == $this->attributeNames[$i])
				{
					if ($i < $numPropertyNames)
					{
						$actualName = $this->propertyNames[$i];	
					}
					else
					{
						$actualName = null;
					}
					
					break;
				}
			}

			if (!is_null($actualName))
			{
				$values[$actualName] = $attrValue;	
			}
		}

		$top =& $this->digester->peek();
		
		BeanUtils::populate($top, $values);
	}
}
?>
