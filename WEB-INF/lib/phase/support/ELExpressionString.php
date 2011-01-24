<?php
/* $Id: ELExpressionString.php 188 2005-04-07 04:52:31Z mojavelinux $
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
import('horizon.util.logging.Logger');

/**
 * Represents a mixture of strings and Expressions.
 *
 * @package phase.support
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class ELExpressionString
{
	var $elements = array();

	function ELExpressionString(&$elements)
	{
		$this->elements =& $elements;
	}

	function &evaluate()
	{
		$buffer = '';
		$numElements = count($this->elements);
		if ($numElements == 0)
		{
			return 'null';
		}
		else
		{
			$evaluatedElements = array();
			for ($i = 0; $i < $numElements; $i++)
			{
				if (is_string($this->elements[$i]))
				{
					$evaluatedElements[] = StringUtils::quote($this->elements[$i]);
				}
				else
				{
					$evaluatedElements[] = 'ConvertUtils::convert(' . $this->elements[$i]->evaluate() . ', \'string\')';
				}
			}

			// we don't need makeCopy() here since it only makes sense to establish a reference with an object,
			// however we cannot get an object if we are concatenating strings
			$buffer = implode(' . ', $evaluatedElements);
		}

		return $buffer;
	}

	function &getLog()
	{
		return Logger::getLogger('phase.support.ELExpressionString');
	}
}
?>
