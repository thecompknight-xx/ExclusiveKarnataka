<?php
/* $Id: PageContext.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('phase.support.ELEvaluator');

/**
 * A <b>PageContext</b> instance provides access to all the namespaces
 * associated with a PSP page.
 *
 * When tucked into a PSP page, the Phase template format similar to JSP, it
 * provides access to page attributes and implicit objects.
 *
 * @package phase.runtime
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @access public
 *
 * TODO: we are not throwing errors if invalid session
 * TODO: ApplicationDispatcher should be available through servlet context
 */
class PageContext
{
	/**
	 * Page scoped attributes
	 * @var array
	 */
	var $attributes;

	/**
	 * The request associated with this page
	 * @var HttpServletRequest
	 */
	var $request;

	/**
	 * The response associated with this page
	 * @var HttpServletResponse
	 */
	var $response;

	var $session;

	var $context;

	function initialize(&$context, &$request, &$response)
	{
		// @todo: this is a hack until I can figure out how to pass in the servlet
		$this->context =& $context;

		$this->request =& $request;
		$this->response =& $response;

		// should we not create the session at this point?
		$this->session =& $request->getSession(false);
	}

	function &getRequest()
	{
		return $this->request; 
	}

	function &getResponse()
	{
		return $this->response;
	}

	/**
	 * Get the session, reattempting to grab it from request
	 * if it doesn't exist.  The problem is that at any point,
	 * the session could spring into action, so we have to keep
	 * checking as long as we assume this scenario.
	 */
	function &getSession()
	{
		if (is_null($this->session))
		{
			$this->session =& $this->request->getSession(false);
		}

		return $this->session;
	}

	function &getServletContext()
	{
		return $this->context;
	}

	/**
	 * Get the attribute in the specified scope, returning null if not found
	 *
	 * @param string $name Name of attribute
	 * @param string $scope (optional) Scope where we will find the attribute
	 * @return mixed
	 */
	function &getAttribute($name, $scope = 'page')
	{
		switch ($scope)
		{
			case 'page':
				if (isset($this->attributes[$name]))
				{
					$return =& $this->attributes[$name];
				}
				else
				{
					$return =& ref(null);
				}
			break;

			case 'request':
				$return =& $this->request->getAttribute($name);
			break;

			case 'session':
				if (!is_null($this->getSession()))
				{
					$return =& $this->session->getAttribute($name);
				}
				else
				{
					$return =& ref(null);
				}

			break;

			case 'application':
				$return =& $this->context->getAttribute($name);
			break;

			default:
				throw_exception(new IllegalArgumentException('Unknown scope specified: ' . $scope));
				$return =& ref(null);
		}
		return $return;
	}

	/**
	 * Set the attribute in the specified scope
	 *
	 * @param string $name Name of the attribute
	 * @param mixed $value Value of the attribute
	 * @param string $scope (optional) Scope in which variable should be set
	 * @return void
	 */
	function setAttribute($name, &$value, $scope = 'page')
	{
		switch ($scope)
		{
			case 'page':
				$this->attributes[$name] =& $value;
			break;

			case 'request':
				$this->request->setAttribute($name, $value);
			break;

			case 'session':
				if (!is_null($this->getSession()))
				{
					$this->session->setAttribute($name, $value);
				}
			break;

			case 'application':
				// NOTE: not guaranteed to work due to synchronization limitations
				return $this->context->setAttribute($name, $value);
			break;

			default:
		}
	}

	/**
	 * Remove the named attribute in the given scope.  If the scope is <kbd>null</kbd>
	 * then try to remove the attribute from all scopes.
	 *
	 * @param string $name Name of the attribute to remove
	 * @param string $scope (optional) The scope from which to remove the attribute
	 * @return void
	 */
	function removeAttribute($name, $scope = null)
	{
		switch ($scope)
		{
			case 'page':
				unset($this->attributes[$name]);
			break;

			case 'request':
				$this->request->removeAttribute($name);	
			break;

			case 'session':
				if (!is_null($this->getSession()))
				{
					$this->session->removeAttribute($name);
				}
			break;

			case 'application':
				// NOTE: not guaranteed to work due to synchronization limitations
				$this->context->removeAttribute($name);
			break;

			// if scope is not set try them all
			// QUESTION: is this the desired behavior?
			default:
				$this->removeAttribute($name, 'page');
				$this->removeAttribute($name, 'request');
				$this->removeAttribute($name, 'session');
				$this->removeAttribute($name, 'application');
		}
	}

	/**
	 * Get the scope in which the named attribute exists, <kbd>null</kbd> if it does
	 * not exist in any scope
	 *
	 * @param string $name Name of the attribute
	 * @return string
	 */
	function getAttributesScope($name)
	{
		if (!is_null($this->getAttribute($name, 'page')))
		{
			return 'page';
		}

		if (!is_null($this->getAttribute($name, 'request')))
		{
			return 'request';
		}

		if (!is_null($this->getSession()))
		{
			if (!is_null($this->getAttribute($name, 'session')))
			{
				return 'session';
			}
		}

		if (!is_null($this->getAttribute($name, 'application')))
		{
			return 'application';
		}
		
		return null;
	}

	/**
	 * Find an attribute with the specified name in any scope, searching from
	 * page scope up to application scope.
	 *
	 * @param string $name Name of the attribute
	 * @return mixed
	 */
	function &findAttribute($name)
	{
		$value =& $this->getAttribute($name, 'page');
		if (!is_null($value))
		{
			return $value;
		}

		$value =& $this->getAttribute($name, 'request');
		if (!is_null($value))
		{
			return $value;
		}

		if (!is_null($this->getSession()))
		{
			$value =& $this->getAttribute($name, 'session');
			if (!is_null($value))
			{
				return $value;
			}
		}

		// either we find it in application or it is null
		return $this->getAttribute($name, 'application');
	}

	/**
	 * Get the names of the attributes in a specified scope
	 *
	 * @param string $scope Scope in which to search for attributes
	 * @return array
	 */
	function getAttributeNamesInScope($scope)
	{
		switch ($scope)
		{
			case 'page':
				return array_keys($this->attributes);
			break;

			case 'request':
				return $this->request->getAttributeNames();
			break;

			case 'session':
				if (!is_null($this->getSession()))
				{
					return $this->session->getAttributeNames();
				}
			break;

			case 'application':
				return $this->context->getAttributeNames();
			break;

			default:
				return array();
		}
	}

	/**
	 * A convenience method for calling the {@link
	 * ServletContext::getRequestDispatcher()} method on our servlet context
	 * and calling the resulting {@link RequestDispatcher::doForward()} method.
	 *
	 * @todo we want to look at why we don't use the getRequestDispatcher on the
	 * request
	 */
	function doForward($uri)
	{
		$rd =& $this->context->getRequestDispatcher($uri);
		$rd->doForward($this->request, $this->response);
	}

	/**
	 * A convenience method for calling the {@link
	 * ServletContext::getRequestDispatcher()} method on our servlet context
	 * and calling the resulting {@link RequestDispatcher::doInclude()} method.
	 *
	 * @todo we want to look at why we don't use the getRequestDispatcher on the
	 * request
	 */
	function doInclude($uri)
	{
		$rd =& $this->context->getRequestDispatcher($uri);
		return $rd->doInclude($this->request, $this->response);
	}

	/**
	 * Evaluate template text as an expression string and return the result as
	 * a string.  This method is used primarily by the PhaseParser when the
	 * el-ignore option is set to false in the servlet initialization
	 * parameters.
	 *
	 * @param string $text The template text to be evaluated.
	 * @return string
	 */
	function evaluateTemplateText($text)
	{
		return ELEvaluator::evaluate('templateText', $text, 'string', $this);
	}
}
?>
