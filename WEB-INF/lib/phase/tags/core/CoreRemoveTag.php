<?php
/* $Id: CoreRemoveTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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
import('phase.tagext.BodyTagSupport');
import('phase.support.ELEvaluator');

/**
 * @package phase.tags.core
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @access public
 *
 * TODO: handle case when 'var' is an EL expression and we have to use the property attribute
 */
class CoreRemoveTag extends TagSupport
{
	var $var;

	var $scope;

	function CoreRemoveTag()
	{
		$this->init();
	}

	function init()
	{
		$this->scope = 'page';
		$this->var = null;
	}

	function setVar($var)
	{
		$this->var = $var;
	}

	// @todo perhaps add a Util call to resolve scope
	function setScope($scope)
	{
		$this->scope = $scope;
	}

	function doStartTag()
	{
		$this->pageContext->removeAttribute($this->var, $this->scope);
		return parent::doStartTag();
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
