<?php
/* $Id: HtmlLinkTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.net.MalformedURLException');

import('studs.StudsConstants');
import('studs.util.RequestUtils');
import('studs.taglib.TagUtils');
import('studs.taglib.html.BaseInteractiveTag');

import('phase.tagext.BodyTagSupport');
import('phase.support.ELEvaluator');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package phase.tags.html
 * @access public
 */
class HtmlLinkTag extends BaseInteractiveTag
{
	var $anchor;

	var $href;

	var $page;

	var $action;

	var $forward;
	
	var $target;

	/**
	 * The name of a scoped variable containing a map of parameters
	 */
	var $name;

	/**
	 * The scope under which to search for variables
	 */
	var $scope;

	function HtmlLinkTag()
	{
		$this->init();
	}

	function init()
	{
		$this->anchor = null;
		$this->href = null;
		$this->page = null;
		$this->action = null;
		$this->forward = null;
		$this->target = null;
		$this->name = null;
		$this->scope = null;
	}

	function setAnchor($anchor)
	{
		$this->anchor = $anchor;
	}

	function setHref($href)
	{
		$this->href = $href;
	}

	function setPage($page)
	{
		$this->page = $page;
	}

	function setAction($action)
	{
		$this->action = $action;
	}

	function setForward($forward)
	{
		$this->forward = $forward;
	}

	function setTarget($target)
	{
		$this->target = $target;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function setScope($scope)
	{
		$this->scope = $scope;
	}

	function doStartTag()
	{
		return c('Tag::EVAL_BODY_BUFFERED');
	}

	function doEndTag()
	{
		$this->evaluateExpressions();

		$url = $this->resolveUrl();

		$out = '<a href="' . $url . '"' . $this->renderStyleAttributes() . $this->renderMetaAttributes() . $this->renderEventAttributes() . '>';
		$out .= $this->bodyContent;
		$out .= '</a>';

		echo $out;
		return c('Tag::EVAL_PAGE');
	}

	function evaluateExpressions()
	{
	}

	/**
	 * Based on the URL-related attributes specified in the taglib,
	 * determine the URL that will be used for this tag and return
	 * it as a string.
	 *
	 * @return string The qualified URL to which this tag points
	 */
	function resolveUrl()
	{
		$url = '';
		$request =& $this->pageContext->getRequest();

		if (!is_null($this->forward))
		{
			$moduleConfig =& TagUtils::getModuleConfig($this->pageContext);
			$forwardConfig =& $moduleConfig->findForwardConfig($this->forward);
			if (is_null($forwardConfig))
			{
				throw_exception(new MalformedURLException('Cannot locate forward named ' . $this->forward));
				return;
			}

			$path = $forwardConfig->getPath();	
			if ($path{0} == '/')
			{
				$url .= $request->generateControllerPath($request->getContextPath(), true) . RequestUtils::forwardURL($request, $forwardConfig, $moduleConfig);
			}
			else
			{
				$url .= $path;
			}
		}
		// href is not processed
		elseif (!is_null($this->href))
		{
			$url = $this->href;
		}
		elseif (!is_null($this->action))
		{
			$url = TagUtils::getActionMappingURL($this->action, $this->pageContext);
		}
		elseif (!is_null($this->page))
		{
			// TODO: how do we know if this needs to pass through the controller?
			$url = $request->generateControllerPath($request->getContextPath(), false) . $this->page;
		}

		if (!is_null($this->name))
		{
			if (!is_null($this->scope))
			{
				$paramMap =& $this->pageContext->getAttribute($this->name, $this->scope);
			}
			else
			{
				$paramMap =& $this->pageContext->findAttribute($this->name);
			}

			// TODO: clean this up, make util method
			if (!is_null($paramMap))
			{
				if (count($paramMap) > 0)
				{
					$url .= (strpos($url, '?') === false) ? '?' : '&amp;';
				}

				$params = array();
				foreach ($paramMap as $name => $value)
				{
					// TODO: url encode value!!
					$params[] = $name . '=' . $value;
				}

				$url .= implode('&amp;', $params);
			}
		}

		if (!is_null($this->anchor))
		{
			$url .= '#' . $this->anchor;
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
