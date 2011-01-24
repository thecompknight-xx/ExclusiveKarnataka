<?php
/* $Id: CoreImportTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('phase.tagext.TagSupport');
import('phase.support.ELEvaluator');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package phase.tags.core
 * @access public
 *
 * TODO: allow a context path, save result to var, specify parameters
 */
class CoreImportTag extends TagSupport // implements ELTag
{
	var $url;

	var $urlExpression;

	// also context, var, scope

	function CoreImportTag()
	{
		$this->init();
	}

	function init()
	{
		unset($this->url);
		$this->urlExpression = null;
	}

	function setUrl($url)
	{
		$this->urlExpression = $url;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();

		// handle case of external URL
		if (preg_match(';^(ftp|https?)://;', $this->url))
		{
			include $this->url;
		}
		// handle case of an absolute local URL
		elseif ($this->url{0} == '/')
		{
			$this->pageContext->doInclude($this->url);
		}
		// handle the case of a relative local URL
		// @fixme this is a dirty hack!!
		else
		{
			$request =& $this->pageContext->getRequest();
			$this->pageContext->doInclude(dirname($request->getServletPath()) . '/' . $this->url);
		}

		return parent::doStartTag();
	}

	function evaluateExpressions()
	{
		if (!is_null($this->urlExpression))
		{
			$this->url = ELEvaluator::evaluate('url', $this->urlExpression, 'string', $this->pageContext);
		}
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
