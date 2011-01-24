<?php
/* $Id: ServerInfo.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * Simple utility for globally grabbing the server information from the underlying
 * storage mechanism, in this case the PHP implicit variables and/or methods.
 *
 * @package stratus.util
 * @author Dan Allen
 * @version $Revision: 188 $ $Date: 2005-04-07 00:52:31 -0400 (Thu, 07 Apr 2005) $
 */
class ServerInfo
{
	function getServerInfo()
	{
		$info = array();
		if (isset($_SERVER['SERVER_SOFTWARE']))
		{
			$info[] = $_SERVER['SERVER_SOFTWARE'];
		}

		// make sure that we have the PHP version tacked on the end, don't trust SERVER_SOFTWARE setting
		if (strstr(implode(' ', $info), 'PHP') === false)
		{
			$info[] = 'PHP/' . PHP_VERSION;
		}

		return implode(' ', $info);
	}
}
?>
