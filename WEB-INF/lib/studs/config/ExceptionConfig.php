<?php
/* $Id: ExceptionConfig.php 188 2005-04-07 04:52:31Z mojavelinux $
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

/**
 * @package studs.config
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class ExceptionConfig extends Object
{
	var $configured = false;

	var $handler = 'studs.action.ExceptionHandler';

	var $key = null;

	var $path = null;

	var $scope = 'request';

	var $type = null;

	function getHandler()
	{
		return $this->handler;
	}

	function setHandler($handler)
	{
		$this->handler = $handler;
	}

	function getKey()
	{
		return $this->key;
	}

	function setKey($key)
	{
		$this->key = $key;
	}

	function getPath()
	{
		return $this->path;
	}

	function setPath($path)
	{
		$this->path = $path;
	}

	function getScope()
	{
		return $this->scope;
	}

	function setScope($scope)
	{
		$this->scope = $scope;
	}

	function getType()
	{
		return $this->type;
	}

	function setType($type)
	{
		$this->type = $type;
	}

	function freeze()
	{
		$this->configured = true;
	}
}
?>
