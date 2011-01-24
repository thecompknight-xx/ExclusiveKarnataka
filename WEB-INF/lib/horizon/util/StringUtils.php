<?php
/* $Id: StringUtils.php 321 2006-03-11 05:07:42Z mojavelinux $
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

/**
 * General methods for manipulating and preparing strings.
 *
 * @package horizon.util
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @access public
 */
class StringUtils
{
	function escapeString($string, $charsToEscape = array('\''), $escapeChar = '\\')
	{
		foreach ($charsToEscape as $charToEscape)
		{
			$string = str_replace($charToEscape, $escapeChar . $charToEscape, $string);
		}

		return $string;
	}

	function quote($string, $quote = '\'')
	{
		$charsToEscape = array($quote);
		if ($quote == '"')
		{
			$charsToEscape[] = '$';
		}

		return $quote . StringUtils::escapeString($string, $charsToEscape) . $quote;
	}

	function substringAfterLast($string, $separator)
	{
		if (is_null($string)) {
			return null;
		}

		$slices = explode($separator, $string);
		$last = end($slices);
		return $last;
	}
}
?>
