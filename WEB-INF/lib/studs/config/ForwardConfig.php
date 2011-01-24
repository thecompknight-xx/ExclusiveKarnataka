<?php
/* $Id: ForwardConfig.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 */
class ForwardConfig extends Object
{
	var $configured = false;

	var $name = null;

	var $path = null;

	var $redirect = false;

	// @todo change this to "module" which will default to "/"
	var $contextRelative = false;

	function ForwardConfig($name = null, $path = null, $redirect = false, $contextRelative = false)
	{
		$this->name = $name;
		$this->path = $path;
		$this->redirect = $redirect;
		$this->contextRelative = $contextRelative;
	}

	function getName()
	{
		return $this->name;
	}

	function setName($name)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->name = $name;
	}

	function getPath()
	{
		return $this->path;
	}

	function setPath($path)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->path = $path;
	}

	function isRedirect()
	{
		return $this->redirect;
	}

	function setRedirect($redirect)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->redirect = ConvertUtils::convert($redirect, 'boolean');
	}

	function isContextRelative()
	{
		return $this->contextRelative;
	}

	function setContextRelative($contextRelative)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->contextRelative = ConvertUtils::convert($contextRelative, 'boolean');
	}

	function freeze()
	{
		$this->configured = true;
	}
}
?>
