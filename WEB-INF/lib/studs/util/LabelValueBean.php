<?php
/* $Id: LabelValueBean.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * A simple bean that contains a getter for the label and a
 * getter for the value.  Primarily intended for multi-select
 * and multi-box options in html forms.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.util
 * @access public
 */
class LabelValueBean extends Object
{
	var $label = null;

	var $value = null;

	function LabelValueBean($label, $value)
	{
		$this->label = $label;
		$this->value = $value;
	}

	function getLabel()
	{
		return $this->label;
	}

	function getValue()
	{
		return $this->value;
	}

	function equals($o)
	{
		if (is_null($o) || is_null($o->getValue()))
		{
			return is_null($this->value);
		}
		else
		{
			return ($this->value == $o->getValue());
		}
	}
}
?>
