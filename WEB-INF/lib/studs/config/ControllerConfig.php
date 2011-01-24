<?php
/* $Id: ControllerConfig.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.beanutils.ConvertUtils');

/**
 * @package studs.config
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class ControllerConfig extends Object
{
	var $configured = false;

	var $contentType = 'text/html';

	var $forwardPattern = null;

	var $inputForward = false;

	var $locale = true;

	var $maxFileSize = '250M';

	var $nocache = false;

	var $processorClass = 'studs.action.RequestProcessor';

	var $tempDir = null;

	function getContentType()
	{
		return $this->contentType;
	}

	function setContextType($contentType)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->contentType = $contentType;
	}

	function getForwardPattern()
	{
		return $this->forwardPattern;
	}

	function setForwardPattern($forwardPattern)
	{
		$this->forwardPattern = $forwardPattern;
	}

	/**
	 * @return boolean
	 */
	function isInputForward()
	{
		return $this->inputForward;
	}

	function setInputForward($inputForward)
	{
		// it is necessary to use ConvertUtils since digester will be passing a string
		$this->inputForward = ConvertUtils::convert($inputForward, 'boolean');
	}

	/**
	 * @return boolean
	 */
	function getLocale()
	{
		return $this->locale;
	}

	function setLocale($locale)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		// it is necessary to use ConvertUtils since digester will be passing a string
		$this->locale = ConvertUtils::convert($locale, 'boolean');
	}

	function getMaxFileSize()
	{
		return $this->maxFileSize;
	}

	function setMaxFileSize($maxFileSize)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->maxFileSize = $maxFileSize;
	}

	function isNocache()
	{
		return $this->nocache;
	}

	function setNocache($nocache)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->nocache = $nocache;
	}

	function getProcessorClass()	
	{
		return $this->processorClass;
	}

	function setProcessorClass($processorClass)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->processorClass = $processorClass;
	}

	function getTempDir()
	{
		return $this->tempDir;
	}

	function setTempDir($tempDir)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->tempDir = $tempDir;
	}

	function freeze()
	{
		$this->configured = true;
	}
}
?>
