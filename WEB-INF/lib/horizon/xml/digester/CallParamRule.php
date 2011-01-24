<?php
/* $Id: CallParamRule.php 370 2006-10-17 05:19:38Z mojavelinux $
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
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan, Scott Sanders
 * @package horizon.xml.digester
 */
class CallParamRule extends Rule
{
	var $attributeName = null;

	var $paramIndex = 0;

	var $fromStack = false;

	var $stackIndex = 0;

    /**
     * Stack is used to allow nested body text to be processed.
     * Lazy creation.
	 * @var array
     */
	var $bodyTextStack = null;

	/**
	 * Construct a "call parameter" rule
	 * If the attributeName is null, it will take it from the body if the stackIndex is
	 * false or from the stack using the stackIndex value.  If the attributeName is not
	 * null, it will use the value of the attribute.
	 */
	function CallParamRule($paramIndex, $attributeName = null, $stackIndex = -1)
	{
		$this->paramIndex = $paramIndex;
		$this->attributeName = $attributeName;
		if ($stackIndex >= 0)
		{
			$this->fromStack = true;
			if ($stackIndex > 0)
			{
				$this->stackIndex = $stackIndex;
			}
		}
	}

	function begin($namespace, $name, $attributes)
	{
		$param = null;

		if (!is_null($this->attributeName))
		{
			if (array_key_exists($this->attributeName, $attributes))
			{
				$param = $attributes[$this->attributeName];
			}
		}
		elseif ($this->fromStack)
		{
			$param =& $this->digester->peek($this->stackIndex);
		}

		// Have to save the param object to the param stack frame here.
		// Can't wait until end(). Otherwise, the object will be lost.
		// We can't save the object as instance variables, as
		// the instance variables will be overwritten
		// if this CallParamRule is reused in subsequent nesting.
		if (!is_null($param))
		{
			$parameters =& $this->digester->peekParams();
			$parameters[$this->paramIndex] =& $param;
		}
	}

	function body($namespace, $name, $text)
	{
		if (is_null($this->attributeName) && !$this->fromStack)
		{
			if (is_null($this->bodyTextStack))
			{
				$this->bodyTextStack = array();
			}
			
			//array_push($this->bodyTextStack, &$text);
			// NOTE: use alternative syntax to prevent call_time_pass_by_reference warning
			$this->bodyTextStack[] =& $text;
		}
	}

	function end($namespace, $name)
	{
		if (!is_null($this->bodyTextStack) && count($this->bodyTextStack) > 0)
		{
			$parameters =& $this->digester->peekParams();
			$parameters[$this->paramIndex] =& $this->bodyTextStack[count($this->bodyTextStack) - 1];
			array_pop($this->bodyTextStack);
		}
	}
}
?>
