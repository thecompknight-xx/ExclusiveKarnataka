<?php
/* $Id: ApplicationContext.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('stratus.http.HttpServletRequest');
import('stratus.core.ApplicationDispatcher');
import('stratus.util.ServletUtils');
import('stratus.util.ServerInfo');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package stratus.core
 */
class ApplicationContext extends Object /* implements ServletContext */
{
	var $attributes = null;

	var $parameters = null;

	var $context = null;

	var $basePath = null;

	function ApplicationContext($basePath, &$context)
	{
		$this->context =& $context;
		$this->mergeParameters();
		$this->basePath = $basePath;
	}

	function getServerInfo()
	{
		return ServerInfo::getServerInfo();
	}

	function getServletContextName()
	{
		return $this->context->getDisplayName();
	}

	// QUESTION: make private?
	function mergeParameters()
	{
		/* This logic was causing the parameters to never be populated!!
		if (!is_null($this->parameters))
		{
			return;
		}*/

		$this->parameters = $this->context->findParameters();
		$this->context->setContextModified(true);
	}

	function clearAttributes()
	{
		// only modified if attributes weren't empty
		if (count($this->attributes) > 0)
		{
			$this->attributes = array();
			$this->context->setContextModified(true);
		}
	}

	function &getAttribute($name)
	{
		$nil =& ref(null);
		if (!isset($this->attributes[$name]))
		{
			return $nil;
		}

		return $this->attributes[$name];
	}

	function setAttribute($name, &$value)
	{
		// name cannot be null
		if (is_null($name))
		{
			throw_exception(new IllegalArgumentException('Attribute name cannot be null [Application Context]'));
			return;
		}

		// null value is the same as removeAttribute()
		if (is_null($value))
		{
			$this->removeAttribute($name);
		}
		else
		{
			$this->attributes[$name] =& $value;
			$this->context->setContextModified(true);
		}
	}

	function removeAttribute($name)
	{
		if (isset($this->attributes[$name]))
		{
			unset($this->attributes[$name]);
			$this->context->setContextModified(true);
		}
	}

	function getAttributeNames()
	{
		return array_keys($this->attributes);
	}

	function getInitParameter($name)
	{
		if (!isset($this->parameters[$name]))
		{
			return null;
		}

		return $this->parameters[$name];
	}

	function getInitParameterNames()
	{
		return array_keys($this->parameters);
	}

	function getMajorVersion()
	{
		$version = phpversion();
		return substr($version, 0, strpos($version, '.'));
	}

	function getMinorVersion()
	{
		$version = phpversion();
		return substr($version, strpos($version, '.') + 1);
	}

    /**
     * Return the MIME type of the specified file, or <kbd>null</kbd> if
     * the MIME type cannot be determined.
     *
     * @param string $file Filename for which to identify a MIME type
	 * @return string The MIME type matching the extension
     */
	function getMimeType($file)
	{
		if (is_null($file))
		{
			return null;
		}

		$pathinfo = pathinfo($file);
		if (!isset($pathinfo['extension']))
		{
			return null;
		}

		return $this->context->findMimeMapping($pathinfo['extension']);
	}

    /**
     * Return the real path for a given virtual path
     *
     * @param string $path The path to the desired resource
     */	
	function getRealPath($path)
	{
		return $this->basePath . $path;
	}

	function &getRequestDispatcher($path)
	{
		$path = ServletUtils::normalize($path);

		if (is_null($path))
		{
			return null;
		}

		if ($path[0] != '/')
		{
			$path = '/' . $path;
		}

		$contextPath = $this->context->getPath();		
		$relativeURI = $path;
		$queryString = null;
		$question = strpos($path, '?');
		if ($question !== false)
		{
			$relativeURI = substr($path, 0, $question);
			$queryString = substr($path, $question + 1);
		}

		$request =& new HttpServletRequest();
		$request->setContext($this->context);
		$request->setContextPath($this->context->getPath());
		$request->setRequestURI($contextPath . $relativeURI);
		$request->setQueryString($queryString);

		$wrapper =& $this->context->map($request);
		if (is_null($wrapper))
		{
			return null;
		}

		$instance =& new ApplicationDispatcher(
			$wrapper,
			$request->getServletPath(),
			$request->getPathInfo(),
			$request->getQueryString(),
			null
		);

		return $instance;
	}

	/**
	 * Returns a directory-like listing of all the paths to resources within
	 * the web application whose longest sub-path matches the supplied path
	 * argument. The returned paths are all relative to the root of the web
	 * application and have a leading '/'.
	 *
	 * @param string $path The partial path used to match the resources (must begin with '/')
	 *
	 * @return array String of resource pathnames relative to the path provided, or null
	 * if the path could not be matched (the resulting strings will have to be resolved against
	 * getResource()
	 */
	function getResourcePaths($path)
	{
		$path = ServletUtils::normalize($path);

		if (is_null($path))
		{
			return null;
		}

		$dir =& Clazz::getResource($this->basePath . $path);
		if (is_null($dir))
		{
			return null;
		}

		$filenames = $dir->listFileNames();
		$paths = array();
		foreach ($filenames as $filename)
		{
			$paths[] = $path . ($path == '/' ? '' : '/') . $filename;
		}

		return $paths;
	}

	/**
	 * @return File
	 */
	function &getResource($path)
	{
		$path = ServletUtils::normalize($path);

		if (is_null($path))
		{
			$nil =& ref(null);
			return $nil;
		}

		$clazz =& Clazz::getResource($this->basePath . $path);
		return $clazz;
	}

	/**
	 * This obviously gets crazy in the catalina version, but basically we are just
	 * prepending the absolute base path of our servlet context and turning that into a stream.
	 * It is a requirement that this path begin with a / specifying that it refers to the
	 * root of the servlet context.  If the path is not found or does not begin with /
	 * <kbd>null</kbd> is returned.
	 *
	 * @param string $path
	 * @return Reader
	 */
	function &getResourceAsStream($path)
	{
		$path = ServletUtils::normalize($path);

		if (is_null($path))
		{
			$nil =& ref(null);
			return $nil;
		}

		$clazz =& Clazz::getResourceAsStream($this->basePath . $path);
		return $clazz;
	}
}
?>
