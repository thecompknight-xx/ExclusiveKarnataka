<?php
/* $Id: DefaultServlet.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.util.logging.Logger');
import('stratus.ServletConstants');
import('stratus.http.HttpServletRequest');
import('stratus.servlets.HttpServlet');
import('stratus.util.ServletUtils');
import('horizon.beanutils.ConvertUtils');

/**
 * @package stratus.servlets
 * @access public
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class DefaultServlet extends HttpServlet
{
	var $welcomeFiles = array();

	var $listings = true;

	var $welcomes = true;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('stratus.servlets.DefaultServlet');
		return $logger;
	}

	function destroy()
	{
		; // no actions necessary
	}

	function init()
	{
		// set properties from initialization parameters
		$servletConfig =& $this->getServletConfig();
		if (!is_null($servletConfig->getInitParameter('listings')))
		{
			$this->listings = ConvertUtils::convert($servletConfig->getInitParameter('listings'), 'boolean');
		}

		if (!is_null($servletConfig->getInitParameter('welcomes')))
		{
			$this->welcomes = ConvertUtils::convert($servletConfig->getInitParameter('welcomes'), 'boolean');
		}

		// initialize set of welcome files
		$servletContext =& $this->getServletContext();
		$this->welcomeFiles = $servletContext->getAttribute(c('ServletConstants::WELCOME_FILES_KEY'));
		if (is_null($this->welcomeFiles))
		{
			$this->welcomeFiles = array();
		}
	}

	function doGet(&$request, &$response)
	{
		$this->serveResource($request, $response, true);
	}

	function doPost(&$request, &$response)
	{
		$this->doGet($request, $response);
	}

	/**
	 * @access protected
	 */
	function getRelativePath(&$request)
	{
		$result = $request->getPathInfo();
		if (strlen($result) == 0)
		{
			$result = '/';
		}

		return ServletUtils::normalize($result);
	}

	/**
	 * This method is the main workhorse.  If the request is for a directory,
	 * the first step is to look for welcome files in the configuration and then
	 * check whether they exist.  If that fails, and directory listings are on, a
	 * directory list is sent in HTML format.  If the request is for a file,
	 * the file is served up with the appropriate mime-type.  Any other combination
	 * will return a 404 error.
	 *
	 * NOTE: when listing directories, the servlet path (which is the part of the URL
	 * after the context path that matched us to DefaultServlet) is thrown out when
	 * looking for the directory to list.
	 */
	function serveResource(&$request, &$response, $content)
	{
		$servletPath = $request->getServletPath();

		// identify the requested resource path
		$path = $this->getRelativePath($request);

		// make sure that this path exists on disk
		if (!$this->resourceExists($path))
		{
			$response->sendError(c('HttpServletResponse::SC_NOT_FOUND'), $request->getRequestURI());
			return;
		}

		$resource = $this->getResourcePath($path);

		// if this is a directory, first check welcome files...if that fails
		// see if we can do a listing
		if (is_dir($resource))
		{
			$pathStr = new String($path);

			if ($this->useWelcomeFiles())
			{
				$welcomeFile = $this->_checkWelcomeFiles($path);
				if ($welcomeFile != null)
				{
					$log =& DefaultServlet::getLog();
					$log->debug('Sending redirect to welcome file ' . $welcomeFile); 

					$response->sendRedirect($this->getURL($request, $welcomeFile));
					return;
				}
			}

			if (!$this->allowListings())
			{
				$response->sendError(c('HttpServletResponse::SC_NOT_FOUND'), $request->getRequestURI());
				return;
			}
			else
			{
				if ($content)
				{
					// serve up the directory listing
					$response->setContentType('text/html');
					echo $this->renderListing($request, $servletPath, $path, $resource);
					return;
				}
			}
		}

		if ($content)
		{
			// we are serving up an actual file here, which we know exists
			$servletContext =& $this->getServletContext();
			$contentType = $servletContext->getMimeType($resource);
			if (!is_null($contentType))
			{
				$response->setContentType($contentType);
			}

			$response->addDateHeader('Last-Modified', filemtime($resource));
			$this->output($resource);
		}
	}

    /**
	 * Prefix the context path, our servlet emulator and append the request
	 * parameters to the redirection string before calling sendRedirect.
	 *
	 * @param $request HttpServletRequest
	 * @param $redirectPath string
	 * @return string
	 * @access protected
     */
	function getURL(&$request, $redirectPath)
	{
		$result = '';

		$contextPath = $request->getContextPath();
		// @question: why "not is null" here?
		if (!is_null($contextPath))
		{
			$result = $request->generateControllerPath($contextPath, true);
		}

		$result .= $redirectPath;

		$query = $request->getQueryString();
		if (!is_null($query))
		{
			$result .= '?' . $query;
		}

		return $result;
	}

	function _checkWelcomeFiles($pathname)
	{
		if (substr($pathname, -1) != '/')
		{
			$pathname .= '/';
		}

		// run through welcome files and return the first one that exists
		for ($i = 0; $i < count($this->welcomeFiles); $i++)
		{
			$resourceName = $pathname . $this->welcomeFiles[$i];
			if ($this->resourceExists($resourceName))
			{
				return $resourceName;
			}
		}

		return null;
	}

	/**
	 * Give a relative resource, return the absolute path
	 * on the filesystem to the given resource
	 */
	function getResourcePath($resource)
	{
		return realpath('.') . $resource;
	}

	function resourceExists($resource)
	{
		return file_exists($this->getResourcePath($resource));
	}

	/**
	 * Read the contents of the file to the output context, which
	 * in the case of PHP is equivalent to just dumping it.
	 *
	 * @param string $file The full path of a file on disk
	 * @return void
	 */
	function output($file)
	{
		readfile($file);
	}

	/**
	 * Generate an HTML directory list showing the contents of the directory matching
	 * the path following the servlet pattern.
	 *
	 * @todo I really would like to see this method refactored...it is very procedural!!
	 * @todo instroduce ResourceInfo or some object handler for the resource we are working with
	 *
	 * NOTE: I am unsure how to proceed when the servlet path is not '/'.  Tomcat leaves it
	 * off for the links on each listing, which interrupts the browsing cycle.  If I add it
	 * in each time, then pages which would otherwise be caught by other servlets reveal their code,
	 * such as PSP files.
	 */
	function renderListing(&$request, $servletPath, $path, $resource)
	{
		$contextPath = $request->getContextPath();
		// build our base context path with the controller info
		$basePath = $request->generateControllerPath($contextPath) . $servletPath;

		// directories should end in a '/'
		if (substr($path, strlen($path) - 1) != '/')
		{
			$path .= '/';
		}

        $out = '<html>';
		$out .= '<head>';
		$out .= '<title>Directory Listing For ' . $path . '</title>';
		$out .= '<STYLE><!--
h1 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size: 22px; }
h2 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size: 16px; }
h3 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size: 14px; }
body { font-family: Tahoma,Arial,sans-serif; color: black; background-color: white; }
b { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; }
p { font-family: Tahoma,Arial,sans-serif; background: white; color: black; font-size: 12px; }
a { color: black; }
a.name { color: black; }
hr { color : #525D76; }
th { font-size: 17px; }
--></STYLE>';
        $out .= '</head>';
		$out .= '<body>';
        $out .= '<h1>Directory Listing For ' . $path . '</h1>';
		$out .= '<hr size="1" noshade="noshade" />';
		$out .= '<table width="100%" cellspacing="0" cellpadding="5">';
		$out .= '<tr>';
		$out .= '<th style="text-align: left;">Filename</th>';
		$out .= '<th style="text-align: center;">Size</th>';
		$out .= '<th style="text-align: right;">Last Modified</th>';
		$out .= '</tr>';

		$shade = false;
		// if the path is not '/', then we have a parent
		if ($path != '/')
		{
			$parentPath = substr($path, 0, strrpos(rtrim($path, '/'), '/') + 1);
			$out .= '<tr>';
			$out .= '<td style="text-align: left;"><a href="' . $basePath . $parentPath . '"><tt>';
			$out .= '[Up to parent directory]';
			$out .= '</tt></a></td>';
			$out .= '<td style="text-align: right;"><tt>';
			$out .= '</tt></td>';
			$out .= '<td style="text-align: right;"><tt>';
			$out .= gmdate('D, d M Y H:i:s T', filemtime($resource));
			$out .= '</tt></td>';
			$out .= '</tr>';
			$shade = true;
		}

		$dh = opendir($resource);
		while (($child = readdir($dh)) !== false)
		{
			// don't accept parent, self, or special protected directories
			if (preg_match(';^(\.|\.\.|WEB-INF|META-INF|\.htaccess)$;i', $child))
			{
				continue;
			}

			// don't allow the controller to be seen
			// @todo make the controller script a constant!!
			if ($path == '/' && $child == 'index.php')
			{
				continue;
			}

			$childResource = $resource . DIRECTORY_SEPARATOR . $child;
			// add trailing slash for directories
			if (is_dir($childResource))
			{
				$child .= '/';
			}

			$out .= '<tr' . ($shade ? ' style="background-color: #EEEEEE;"' : '') . '>';
			$out .= '<td style="text-align: left;"><a href="' . $basePath . $path . $child . '"><tt>';
			$out .= $child;
			$out .= '</tt></a></td>';
			$out .= '<td style="text-align: right;"><tt>';
			$out .= (is_file($childResource) ? $this->renderSize(filesize($childResource)) : '');
			$out .= '</tt></td>';
			$out .= '<td style="text-align: right;"><tt>';
			$out .= gmdate('D, d M Y H:i:s T', filemtime($childResource));
			$out .= '</tt></td>';
			$out .= '</tr>';
			$shade = !$shade;
		}

		closedir($dh);

		$out .= '</table>';
		$out .= '<hr size="1" noshade="noshade" />';
		$out .= '<h3>' . ServerInfo::getServerInfo() . '</h3>';
		$out .= '</body>';
		$out .= '</html>';
		return $out;
	}

	/**
	 * Given a file length in bytes, convert those bytes to a human readable
	 * format in kb.
	 *
	 * @param int $size The size in bytes
	 */
	function renderSize($size)
	{
		return (round(($size / 1024) * 10) / 10) . ' kb';
	}

	/**
	 * Return the initialization setting which specifies if the welcome files
	 * will be utilized (if present)
	 *
	 * @return boolean
	 */
	function useWelcomeFiles()
	{
		return $this->welcomes;
	}

	/**
	 * Return the initialization setting which specifies if a directory
	 * listing is permitted (if a directory)
	 *
	 * @return boolean
	 */
	function allowListings()
	{
		return $this->listings;
	}
}
?>
