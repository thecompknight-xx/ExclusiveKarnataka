<?php
/* $Id: TemplateInsertTag.php 205 2005-04-16 18:26:39Z mojavelinux $
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

import('phase.tagext.TagSupport');
import('studs.StudsConstants');
import('studs.taglib.TagUtils');

import('studs.taglib.template.util.ContentMap');
import('studs.taglib.template.util.ContentMapStack');

/**
 * @package studs.taglib.template
 * @author Timur Vafin
 * @author Dan Allen <br />
 * <b>Credits: David Geary</b>
 */
class TemplateInsertTag extends TagSupport
{
	var $template;
	
	var $_map;
	
	function TemplateInsertTag()
	{
		$this->init();
	}

	function init()
	{
		$this->template	= null;
		$this->_map	= null;
	}

	function setTemplate($template)
	{
		$this->template = $template;
	}

	function doStartTag()
	{
		$this->_map	=& new ContentMap();
		
		ContentMapStack::push($this->pageContext, $this->_map);
		
		return c('Tag::EVAL_BODY_INCLUDE');
	}

	function doEndTag()
	{
		$prefix = "";
		$config =& TagUtils::getModuleConfig($this->pageContext);
		if (!is_null($config))
		{
			$prefix = $config->getPrefix();
		}

		// try {	
		$this->pageContext->doInclude($prefix . $this->template);
		// } catch () {
		//}
		
		ContentMapStack::pop($this->pageContext);
		return c('Tag::EVAL_PAGE');
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
