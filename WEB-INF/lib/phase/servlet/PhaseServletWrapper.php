<?php
/* $Id: PhaseServletWrapper.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('phase.PhaseCompilationContext');
import('phase.runtime.PageContext');
import('phase.runtime.TagHandlerPool'); // we need this for custom tags

/**
 * @package phase.servlet
 * @author Dan Allen
 */
class PhaseServletWrapper extends Object
{
	var $config = null;

	var $options = null;

	var $phaseUri = null;

	var $compiledFile = null;

	var $firstTime = true;

	var $reload = true;

	function PhaseServletWrapper(&$config, &$options, $phaseUri)
	{
		$this->config =& $config;
		$this->options =& $options;
		$this->phaseUri = $phaseUri;
		$this->compilationContext =& new PhaseCompilationContext(
			$phaseUri,
			$options,
			$config->getServletContext(),
			$this
		);
	}

	function setReload($reload)
	{
		$this->reload = $reload;
	}

	function service(&$request, &$response)
	{
		if ($this->firstTime)
		{
			$this->compilationContext->compile();
		}

		if ($this->reload)
		{
			$this->getCompiledFile();
		}

		// FIXME: this is a quick hack to give us pageContext
		$pageContext =& new PageContext();
		$pageContext->initialize($this->config->getServletContext(), $request, $response);

		include $this->compiledFile;
	}

	function getCompiledFile()
	{
		$this->compiledFile = $this->compilationContext->load();
		$this->firstTime = false;
		$this->reload = false;

		return $this->compiledFile;
	}

	function &getServletContext()
	{
		return $this->config->getServletContext();
	}

	function &getPhaseEngineContext()
	{
		return $this->compilationContext;
	}
}
?>
