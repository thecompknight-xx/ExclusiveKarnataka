<?php
/* $Id: ContentMapStack.php 370 2006-10-17 05:19:38Z mojavelinux $
 *
 * Copyright 2003-2004 Dan Allen, Mojavelinux.com (dan.allen@mojavelinux.com)
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

import('horizon.beanutils.NestedBeanStack');

/**
 * This class provides access to a stack of ContentMaps in request scope
 * through static methods.
 *
 * @author Timur Vafin <br />
 * <b>Credits:</b> David Geary
 */

def('ContentMapStack::TEMPLATE_STACK_KEY', 'studs.taglib.template.TEMPLATE_STACK');

class ContentMapStack extends Object 
{
	/**
	 * Return a reference to the stack. If there is no stack, one is
	 * created and placed into request scope associated with the
	 * page context.
	 *
	 * @param pc The page context associated with a custom tag.
	 */
	function &getStack(&$pc)
	{
		$s =& $pc->getAttribute(c('ContentMapStack::TEMPLATE_STACK_KEY'), 'request');
		
		if (is_null($s))
		{
			$s =& new NestedBeanStack();
			$pc->setAttribute(c('ContentMapStack::TEMPLATE_STACK_KEY'), $s, 'request');
		}
		
		return $s;
	}

	/**
	 * Peek at the map on top of the stack.
	 * 
	 * @param pc The page context associated with a custom tag.
	 */
	function &peek(&$pc)
	{
		$s =& ContentMapStack::getStack($pc);
		
		return $s->peek();
	}

	/**
	 * Push a content map onto the stack. 
	 *
	 * @param pc The page context associated with a custom tag.
	 * @param map A content map that gets pushed onto the stack.
	 */
	function push(&$pc, $map)
	{
		$s =& ContentMapStack::getStack($pc);
		
		$s->push($map);
  	}

	/**
	 * @param pc The page context associated with a custom tag.
	 */
	function &pop(&$pc)
	{
		$s =& ContentMapStack::getStack($pc);
		
		$return =& $s->pop();
		return $return;
	}
}
?>
