<?php
/* $Id: CoreRedirectTag.php 263 2005-07-12 02:47:28Z mojavelinux $
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


import('phase.tagext.BodyTagSupport');
import('phase.support.ELEvaluator');

/**
 * @package phase.tags.core
 * @author Dan Allen
 */
class CoreRedirectTag extends BodyTagSupport
{
	var $url;

	var $urlExpression;

	var $context;

	var $bodyIsUrl;

	function CoreRedirectTag()
	{
		$this->init();
	}

	function init()
	{
		unset($this->url);
		$this->url = null;
		$this->context = null;
		$this->bodyIsUrl = false;
	}

	function setUrl($url)
	{
		$this->urlExpression = $url;
	}

	function setContext($context)
	{
		$this->context = $context;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();

		if (!is_null($this->url))
		{
			$this->doRedirect($this->url);
			return c('Tag::SKIP_PAGE');
		}

		$this->bodyIsUrl = true;
		return parent::doStartTag();
	}

	/**
	 * If the url attribute was not specified, process the body as the
	 * url and if not empty, redirect
	 */
	function doEndTag()
	{
		if ($this->bodyIsUrl && trim($this->bodyContent) != '')
		{
			$this->doRedirect(trim($this->bodyContent));
			return c('Tag::SKIP_PAGE');
		}

		// if no value is specified, just ignore the taglib and go on
		return c('Tag::EVAL_PAGE');
	}

	function doRedirect($url)
	{
		$response =& $this->pageContext->getResponse();
		// @todo catch any exceptions here
		$response->sendRedirect($this->resolveUrl($url));
	}

	function evaluateExpressions()
	{
		if (!is_null($this->urlExpression))
		{
			$this->url = ELEvaluator::evaluate('url', $this->urlExpression, 'string', $this->pageContext);
		}
	}

	function isAbsoluteUrl($url)
	{
		if (is_null($url))
		{
			return false;
		}

		if (!preg_match(';[a-z]+://;i', $url))
		{
			return false;
		}

		return true;
	}

	function resolveUrl($url)
	{
		if ($this->isAbsoluteUrl($url))
		{
			return $url;
		}

		$request =& $this->pageContext->getRequest();
		if (is_null($this->context))
		{
			if ($this->url{0} == '/')
			{
				return $request->getPageContext() . $this->url; 
			}
		}
		else
		{
			// @todo throw error if context does not begin with /
			if ($this->context != '/')
			{
				return $this->context . $this->url;
			}
		}

		return $url;
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
