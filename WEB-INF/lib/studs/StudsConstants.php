<?php
/* $Id: StudsConstants.php 218 2005-06-21 22:29:30Z mojavelinux $
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
 * General manifest constants for the entire Studs Framework.  These constants
 * are keys used to tuck away data in the servlet context and when passing data
 * to and from the view.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @author Craig R. McClanahan
 * @author David Graham
 * 
 * @package studs
 * @access public
 */

/**
 * The context attribute under which {@link ActionServlet} instance
 * will be stored
 */
def('StudsConstants::ACTION_SERVLET_KEY', 'studs.action.ACTION_SERVLET');

/**
 * The request attribute under which a boolean <kbd>true</kbd> will be
 * stored when the request is cancelled
 */
def('StudsConstants::CANCEL_KEY', 'studs.action.CANCEL');

/**
 * The request parameter whose presence signifies that the form has been
 * cancelled by the user.
 * NOTE: As of right now, dots "." cannot be used in the name since PHP
 * converts them to "_" upon submit.
 */
def('StudsConstants::CANCEL_PARAM', 'studs_action_CANCEL');

def('StudsConstants::MODULE_KEY', 'studs.action.MODULE');

def('StudsConstants::MODULE_PREFIXES_KEY', 'studs.action.MODULE_PREFIXES_KEY');

def('StudsConstants::DATA_SOURCE_KEY', 'studs.action.DATA_SOURCE');

/**
 * The request attribute under which the {@link ActionMessages} objects
 * are stored so that they are accessible by the taglibs for error messages
 */
def('StudsConstants::ERRORS_KEY', 'studs.action.ERRORS');

def('StudsConstants::EXCEPTION_KEY', 'studs.action.EXCEPTION');

def('StudsConstants::LOCALE_KEY', 'studs.action.LOCALE');

/**
 * The request attribute under which the {@link ActionMessages} objects
 * are stored so that they are accessible by the taglibs for normal messages
 */
def('StudsConstants::MESSAGES_KEY', 'studs.action.MESSAGES');

def('StudsConstants::MESSAGE_RESOURCES_KEY', 'studs.action.MESSAGE_RESOURCES');

def('StudsConstants::REQUEST_PROCESSOR_KEY', 'studs.action.REQUEST_PROCESSOR');

def('StudsConstants::SERVLET_MAPPING_KEY', 'studs.action.SERVLET_MAPPING');

def('StudsConstants::TRANSACTION_TOKEN_KEY', 'studs.action.TRANSACTION_TOKEN');

/**
 * The request parameter whose value is used to validate the state of a
 * form submission.
 * NOTE: As of right now, dots "." cannot be used in the name since PHP
 * converts them to "_" upon submit.
 */
def('StudsConstants::TRANSACTION_TOKEN_PARAM', 'studs_action_TRANSACTION_TOKEN');

def('StudsConstants::FORM_KEY', 'studs.action.FORM_KEY');

def('StudsConstants::BEAN_KEY', 'studs.action.BEAN_KEY');

class StudsConstants {}
?>
