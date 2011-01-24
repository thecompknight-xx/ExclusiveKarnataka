<?php
/* $Id: CallMethodRule.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * A manual mapping between the value of a node  and a method on the object at
 * the top of the stack.  If the number of parameters is not provided, it is
 * assumed that the method is equivalent to a bean setter.  If multiple
 * parameters are specified, parameters must be specified along with their
 * positions after the call method rule has been added.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan, Scott Sanders
 * @package horizon.xml.digester
 */
class CallMethodRule extends Rule
{
	var $bodyText = null;

	var $methodName = null;

	var $paramCount = 0;

	function CallMethodRule($methodName, $paramCount = 0)
	{
		$this->methodName = $methodName;
		$this->paramCount = $paramCount;
	}

	function begin($namespace, $name, $attributes)
	{
		if ($this->paramCount > 0)
		{
			$parameters = array_fill(0, $this->paramCount, null);
			$this->digester->pushParams($parameters);
		}
	}

	function body($namespace, $name, $text)
	{
		if ($this->paramCount == 0)
		{
			$this->bodyText = $text;
		}
	}

	function end($namespace, $name)
	{
		$parameters = null;
		if ($this->paramCount > 0)
		{
			$parameters =& $this->digester->popParams();	

			// In the case where the parameter for the method
			// is taken from an attribute, and that attribute
			// isn't actually defined in the source XML file,
			// skip the method call
			if ($this->paramCount == 1 && is_null($parameters[0]))
			{
				return;
			}
		}
		else
		{
			$parameters = array($this->bodyText);
		}

		$top =& $this->digester->peek();	
		MethodUtils::invokeMethod($top, $this->methodName, $parameters);
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
