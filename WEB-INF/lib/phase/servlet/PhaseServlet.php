<?php
/* $Id: PhaseServlet.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('stratus.http.HttpServletResponse');
import('stratus.servlets.HttpServlet');
import('phase.PhaseServletOptions');
import('phase.servlet.PhaseServletWrapper');

/**
 * The Phase engine parses and renders context-sensitive php files.  Unlike
 * regular php files, these files can have taglibs, use the servletContext
 * and other available variables provided by the servlet container
 *
 * @package phase.servlet
 * @author Dan Allen
 */
class PhaseServlet extends HttpServlet
{
	/**
	 * The ServletConfig for this servlet
	 * @var ServletConfig
	 */
	var $config = null;

	/**
	 * The ServletContext for this servlet
	 * @var ServletContext
	 */
	var $context = null;

	/**
	 * The PhaseServletOptions for this servlet
	 * @var PhaseServletOptions
	 */
	var $options = null;

	var $jsps = array();

	function init()
	{
		$this->config =& $this->getServletConfig();
		$this->context =& $this->config->getServletContext();
		$this->options =& new PhaseServletOptions($this->config, $this->context);
	}

	function service(&$request, &$response)
	{
		// check first for an include
		$phaseUri = $request->getAttribute(c('ServletConstants::INC_SERVLET_PATH_KEY'));
		if (is_null($phaseUri))
		{
			$phaseUri = $request->getServletPath();
		}

		$this->servicePhaseFile($request, $response, $phaseUri);
	}

	function servicePhaseFile(&$request, &$response, $phaseUri)
	{
		if (!isset($this->jsps[$phaseUri]))
		{
			$resourceStream =& $this->context->getResourceAsStream($phaseUri);
			if (is_null($resourceStream))
			{
				$response->sendError(c('HttpServletResponse::SC_NOT_FOUND'), $phaseUri);
				return;
			}
			else
			{
				$resourceStream->close();
				if ($e = catch_exception('IOException'))
				{
					// ignore
				}
			}

			$wrapper =& new PhaseServletWrapper(
				$this->config,
				$this->options,
				$phaseUri
			);
			
			$this->jsps[$phaseUri] =& $wrapper;
		}
		else
		{
			$wrapper =& $this->jsps[$phaseUri];
			$this->checkCompile($wrapper);
		}

		$wrapper->service($request, $response);
	}

	/**
	 * Make sure the requested wrapper does not have a cached
	 * compilation which is out of date
	 */
	function checkCompile(&$wrapper)
	{
		$ctxt =& $wrapper->getPhaseEngineContext();
		// try {
		$ctxt->compile();
		// } catch (FileNotFoundException e) {
		// @todo handle this exception
		// }
	}
}
?>
