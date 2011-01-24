<?php
/* $Id: HttpSession.php 352 2006-05-15 04:27:35Z mojavelinux $
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
 * The <strong>HttpSession</strong> is a wrapper around the native
 * session-state implementation in PHP that adheres closely to the
 * Java Servlet Specification.  It stores session variables as 
 * attributes, and also tracks the state of the session.
 *
 * Instances of this class should only be created through the
 * HttpServletRequest#getSession() service.
 *
 * @package stratus.http
 * @author Dan Allen
 */
class HttpSession
{
	var $id = null;

	var $creationTime = null;

	var $attributes = array();

	var $new = false;

	/**
	 * Do not call this directly, should be intialized using the
	 * HttpServletRequest getSession() method to ensure it is only created once
	 */
	function HttpSession($context = null)
	{
		// make sure we only call this once...session_id will not be
		// registered if the session has never been started
		if (!session_id())
		{
			session_set_cookie_params(0, $context == '/' ? $context : $context . '/');
			session_start();
		}

		$this->id = session_id();
		// determine if this is a new session
		if (!isset($_SESSION[c('ServletConstants::SESSION_CREATED_KEY')]))
		{
			$_SESSION[c('ServletConstants::SESSION_CREATED_KEY')] = time() - date('Z');
			$this->new = true;
		}

		$this->creationTime = $_SESSION[c('ServletConstants::SESSION_CREATED_KEY')];
		$this->attributes =& $_SESSION;
	}

	/**
	 * Return the unique id for this session.
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Get the creation time of this session, measured in milliseconds since
	 * midnight January 1, 1970 GMT.
	 */
	function getCreationTime()
	{
		return $this->creationTime;
	}

	/**
	 * Retrieve the specified attribute from the session data, or
	 * <kbd>null</kbd> if this key does not exist.
	 */
	function &getAttribute($name)
	{
		if (!array_key_exists($name, $this->attributes))
		{
			return ref(null);
		}

		return $this->attributes[$name];
	}

	/**
	 * Set the specified attribute in the session data, which
	 * will be persistent between user requests.
	 */
	function setAttribute($name, &$value)
	{
		$this->attributes[$name] =& $value;
	}

	/**
	 * Remove the specified attribute from the session data.
	 * @return void
	 */
	function removeAttribute($name)
	{
		unset($this->attributes[$name]);
	}

	/**
	 * Invalidate the session, clearing any attributes
	 * and resetting the user cookie.
	 */
	function invalidate()
	{
		$this->attributes = array();
		unset($_COOKIE[session_name()]);
		session_destroy();
	}

	/**
	 * Get a string array of session attribute keys.
	 */
	function getAttributeNames()
	{
		return array_keys($this->attributes);
	}

	/**
	 * Determine if this session was created on this request, or
	 * if it had already been started by a previous request.
	 */
	function isNew()
	{
		return $this->new;
	}
}
?>
