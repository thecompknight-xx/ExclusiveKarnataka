<?php
/* $Id: SetNextRule.php 370 2006-10-17 05:19:38Z mojavelinux $
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
import('horizon.xml.digester.Rule');

/**
 * Rule implementation that calls a method on the (top-1) (parent) object,
 * passing the top object (child) as an argument.  It is commonly used to
 * establish parent-child relationships.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan, Scott Sanders
 * @package horizon.xml.digester
 */
class SetNextRule extends Rule
{
	var $methodName = null;

	var $paramType = null;

	function SetNextRule($methodName, $paramType = null)
	{
		$this->methodName = $methodName;
		$this->paramType = $paramType;
	}

	function end($namespace, $name)
	{
		$child =& $this->digester->peek(0);
		$parent =& $this->digester->peek(1);
		
		// make sure our child is of the correct type
		// TODO: this feels like a painful hack, how about a function for this logic?
		if (!is_null($this->paramType) && !is_a($child, StringUtils::substringAfterLast($this->paramType, '.')))
		{
			throw_exception(new IllegalArgumentException("Invalid type for child element, expecting " . $this->paramType));
			return;
		}

		// @todo use MethodUtils here
		$methodName = $this->methodName;
		if (!is_null($parent)) {
			$parent->$methodName($child);
		}
	}
}
?>
