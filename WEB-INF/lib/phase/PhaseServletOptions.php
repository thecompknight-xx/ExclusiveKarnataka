<?php
/* $Id: PhaseServletOptions.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.util.Properties');
import('horizon.beanutils.ConvertUtils');
import('stratus.ServletException');
import('stratus.ServletConstants');

/**
 * A class which holds all of the init parameters for the phase servlet
 *
 * @package phase
 * @author Dan Allen
 */
class PhaseServletOptions /* implements Options */
{
	var $settings = null;

	var $scratchDir = null;

	var $tldResourceDir = '/WEB-INF/tld';

	/**
	 * A flag which determines whether EL expressions in template text will be
	 * evaluated.
	 * @param boolean
	 */
	var $elIgnored = true;

	/**
	 * @param ServletConfig $config Servlet environment config
	 * @param ServletContext $context Servlet context
	 */
	function PhaseServletOptions(&$config, &$context)
	{
		$this->settings =& new Properties();

		$opts = $config->getInitParameterNames();
		foreach ($opts as $name)
		{
			$this->setProperty($name, $config->getInitParameter($name));
		}

		// the scratchdir either has to be absolute or relative to the context root
		$scratchDirParam = $this->getProperty('scratchdir');
		if (!is_empty($scratchDirParam))
		{
			// if the value begins with /WEB-INF/, assume it is inside the
			// web application directory.  Otherwise, assume an absolute path.
			if (strpos($scratchDirParam, '/WEB-INF/') === 0)
			{
				$this->scratchDir =& $context->getResource($scratchDirParam);
			}
			else
			{
				$this->scratchDir =& new File($scratchDirParam);
			}
		}
		else
		{
			$this->scratchDir =& $context->getAttribute(c('ServletConstants::WORK_DIR_ATTR'));
		}

		if (is_null($this->scratchDir))
		{
			throw_exception(new ServletException('No scratch directory specified for page compilation'));
			return;
		}

		if (!is_empty($this->getProperty('ignoreEL')))
		{
			$this->elIgnored = ConvertUtils::convert($this->getProperty('ignoreEL'), 'boolean');
		}

		$tldDirParam = $this->getProperty('tldresourcedir');
		if (!is_empty($tldDirParam))
		{
			$this->tldResourceDir = $tldDirParam;
		}

		// @todo tld stuff
	}

	function getProperty($name)
	{
		return $this->settings->getProperty($name);
	}

	function setProperty($name, $value)
	{
		$this->settings->setProperty($name, $value);
	}

	function &getScratchDir()
	{
		return $this->scratchDir;
	}

	function isElIgnored()
	{
		return $this->elIgnored;
	}

	function getTldResourceDir()
	{
		return $this->tldResourceDir;
	}
}
?>
