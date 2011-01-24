<?php
/* $Id: WebRuleSet.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.xml.digester.RuleSetBase');

/**
 * @package stratus.config
 */
class WebRuleSet extends RuleSetBase
{
	function addRuleInstances(&$digester)
	{
		$digester->addCallMethod('web-app/context-param', 'addParameter', 2);		
		$digester->addCallParam('web-app/context-param/param-name', 0);
		$digester->addCallParam('web-app/context-param/param-value', 1);

		$digester->addCallMethod('web-app/display-name', 'setDisplayName', 0);

		$digester->addObjectCreate('web-app/servlet', 'stratus.core.StandardWrapper');
		$digester->addSetNext('web-app/servlet', 'addChild');

		$digester->addCallMethod('web-app/servlet/init-param', 'addInitParameter', 2);
		$digester->addCallParam('web-app/servlet/init-param/param-name', 0);
		$digester->addCallParam('web-app/servlet/init-param/param-value', 1);	

		$digester->addCallMethod('web-app/servlet/load-on-startup', 'setLoadOnStartup', 0);
		$digester->addCallMethod('web-app/servlet/servlet-class', 'setServletClass', 0);
		$digester->addCallMethod('web-app/servlet/servlet-name', 'setName', 0);

		$digester->addCallMethod('web-app/servlet-mapping', 'addServletMapping', 2);
		$digester->addCallParam('web-app/servlet-mapping/servlet-name', 1);
		$digester->addCallParam('web-app/servlet-mapping/url-pattern', 0);

		$digester->addCallMethod('web-app/session-config/session-timeout', 'setSessionTimeout', 1);
		$digester->addCallParam('web-app/session-config/session-timeout', 0);

		$digester->addCallMethod('web-app/mime-mapping', 'addMimeMapping', 2);
		$digester->addCallParam('web-app/mime-mapping/extension', 0);
		$digester->addCallParam('web-app/mime-mapping/mime-type', 1);
		
		$digester->addCallMethod('web-app/welcome-file-list/welcome-file', 'addWelcomeFile', 0);
	}
}
?>
