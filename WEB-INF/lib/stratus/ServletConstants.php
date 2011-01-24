<?php
/* $Id: ServletConstants.php 283 2005-07-20 05:08:31Z mojavelinux $
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

def('ServletConstants::WELCOME_FILES_KEY', 'stratus.WELCOME_FILES');

def('ServletConstants::WORK_DIR_ATTR', 'stratus.context.tempdir');

def('ServletConstants::INC_REQUEST_URI_KEY', 'stratus.include.request_uri');

def('ServletConstants::INC_SERVLET_PATH_KEY', 'stratus.include.servlet_path');

def('ServletConstants::INC_CONTEXT_PATH_KEY', 'stratus.include.context_path');

def('ServletConstants::INC_QUERY_STRING_KEY', 'stratus.include.query_string');

def('ServletConstants::INC_PATH_INFO_KEY', 'stratus.include.path_info');

def('ServletConstants::SESSION_CREATED_KEY', 'stratus.http.session_creation_time');

def('ServletConstants::CONTROLLER_SCRIPT', '/index.php');

/**
 * Constants key names that are used to communicate state throught
 * request/response.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package stratus
 */
class ServletConstants {}
?>
