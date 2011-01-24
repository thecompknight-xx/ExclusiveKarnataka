<?php
/* $Id: ActionForward.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('studs.config.ForwardConfig');

/**
 * An <b>ActionForward</b> represents a destination to which the
 * controller servlet, {@link ActionServlet}, might be directed to perform
 * a forward or redirect as a result of processing activities of an
 * {@link Action} class.  Instances of this class may be created
 * dynamically as necessary, or configured in association with an
 * {@link ActionMapping} instance for named lookup of potentially multiple
 * destinations for a particular mapping instance.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @author Craig R. McClanahan
 * @package studs.action
 * @access public
 */
class ActionForward extends ForwardConfig
{
	function ActionForward($name = null, $path = null, $redirect = false, $contextRelative = false)
	{
		parent::ForwardConfig($name, $path, $redirect, $contextRelative);
	}
}
?>
