<?php
/* $Id: TemplatePutTag.php 263 2005-07-12 02:47:28Z mojavelinux $
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

import('phase.tagext.BodyTagSupport');
import('horizon.beanutils.ConvertUtils');

import('studs.taglib.template.util.Content');
import('studs.taglib.template.util.ContentMapStack');

/**
 * @package studs.taglib.template
 * @author Timur Vafin
 * @author Dan Allen <br />
 * <b>Credits: David Geary</b>
 * TODO: I don't think that the "direct" is working correctly
 */
class TemplatePutTag extends BodyTagSupport
{
	var $name;
	
	var $content;
	
	var $direct;

	var $_iterator;

	var $_messagesExist;

	function TemplatePutTag()
	{
		$this->init();
	}

	function init()
	{
		$this->name = null;
		$this->content = null;
		$this->direct = false;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function setContent($content)
	{
		$this->content = $content;
	}

	function setDirect($direct)
	{
		$this->direct = ConvertUtils::convert($direct, 'boolean');
	}

	function doEndTag()
	{
		$map =& ContentMapStack::peek($this->pageContext);
		
		$map->put($this->name, new Content($this->getActualContent(), $this->direct));
		
		return c('Tag::EVAL_PAGE');
	}

	function release()
	{
		parent::release();
		$this->init();
	}
	
	function getActualContent()
	{
		$hasBody = $this->hasBody();
		$contentSpecified = (!is_null($this->content));
		
		if (($hasBody && $contentSpecified) || (!$hasBody && !$contentSpecified))
		{
			throw_exception(new PhaseException('Body and content mismatch error'));
			return;
		}
			
		if ($hasBody && $this->direct == false)
		{
			throw_exception(new PhaseException('Body and direct mismatch error'));
			return;
		}

		return $hasBody ? $this->bodyContent : $this->content;
	}
	
	function hasBody()
	{
		$this->bodyContent = trim($this->bodyContent);
		if (is_null($this->bodyContent) || $this->bodyContent == '')
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
?>
