<?php
/* $Id: PropertyMessageResources.php 252 2005-07-05 20:27:30Z mojavelinux $
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

import('horizon.util.Properties');

/**
 * @package studs.util
 * @author Dan Allen
 *
 * TODO: still missing method for getMessageResources() I think this class
 * could be made to be more efficient, it does a lot of wasteful operations
 */
class PropertyMessageResources
{
	var $locales = array();

	var $messages = array();

	var $config = null;

	var $defaultLocale = 'en_US';

	var $returnNull = false;

	var $formats = array();

	var $servletContext = null;

	function PropertyMessageResources($config = null, $returnNull = false)
	{
		$this->config = $config;
		$this->returnNull = $returnNull;
	}

	/**
	 * Sets the configuration value for the message resources, in this
	 * case a resource in the application classpath.
	 */
	function setConfig($config)
	{
		$this->config = $config;
		// somewhat of a hack, but better for memory consumption...
		// break connection with servletContext if we are using
		// the default classloader, rather than the servlet context version
		if (strpos($this->config, '/WEB-INF/') !== 0)
		{
			unset($this->servletContext);
		}
	}

	function getConfig()
	{
		return $this->config;
	}

	function setReturnNull($returnNull)
	{
		$this->returnNull = $returnNull;
	}

	function getReturnNull()
	{
		return $this->returnNull;
	}

	// QUESTION: can we make this happen without this reference???
	function setServletContext(&$servletContext)
	{
		$this->servletContext =& $servletContext;
	}

	function lookupMessage($localeKey, $key)
	{
		$originalKey = $this->messageKey($localeKey, $key);
		$messageKey = null;
		$message = null;
		$underscore = 0;
		$addIt = false;
		
		// run through the regional locales of this language until
		// we find the message, so en_GB -> en, we are getting more
		// general as we go through them
		while (true)
		{
			// make sure we have loaded this locale
			$this->loadLocale($localeKey);

			$messageKey = $this->messageKey($localeKey, $key);
			if (isset($this->messages[$messageKey]))
			{
				$message = $this->messages[$messageKey];
				if ($addIt)
				{
					$this->messages[$originalKey] = $message; 
				}

				return $message;
			}

			$addIt = true;
			$underscore = strrpos($localeKey, '_');
			if ($underscore === false)
			{
				break;
			}

			$localeKey = substr($localeKey, 0, $underscore);
		}

		// TODO: attempt to load defaultLocale if different from current locale

		// as a last resort, try the empty locale, which would exclude an '_' extension
		$localeKey = '';	
		$this->loadLocale($localeKey);
		$messageKey = $this->messageKey($localeKey, $key);

		if (isset($this->messages[$messageKey]))
		{
			$message = $this->messages[$messageKey];
			$this->messages[$originalKey] = $message; 

			return $message;
		}	

		// return an appropriate error indication
		if ($this->returnNull)
		{
			return null;
		}
		else
		{
			return '???' . $this->messageKey($localeKey, $key) . '???';
		}
	}

	/**
	 * Load the messages for a given locale only as needed.
	 */
	function loadLocale($localeKey)
	{
		if (isset($this->locales[$localeKey]))
		{
			return;	
		}

		$this->locales[$localeKey] = $localeKey;
		$props =& new Properties();
		$name = $this->config;

		// resolve the resource stream
		// assume format: /WEB-INF/messages.properties
		if (strpos($name, '/WEB-INF/') === 0)
		{
			if (strlen($localeKey) > 0)
			{
				$name = str_replace('.properties', '_' . $localeKey . '.properties', $name);
			}

			// use ApplicationContext to get resource as stream
			$is =& $this->servletContext->getResourceAsStream($name);
		}
		// assume format: resources.application
		else
		{
			$name = str_replace('.', '/', $name);
			if (strlen($localeKey) > 0)
			{
				$name .= '_' . $localeKey;
			}

			$name .= '.properties';
			// use system class loader to get this resource as a stream
			$is =& Clazz::getResourceAsStream($name);
		}

		if (!is_null($is))
		{
			$props->load($is);
			$is->close();
		}
		
		if ($props->size() < 1)
		{
			return;
		}

		$names = $props->keys();
		foreach ($names as $key)
		{
			$this->messages[$this->messageKey($localeKey, $key)] = $props->getProperty($key);
		}
	}

	function messageKey($localeKey, $key)
	{
		return $localeKey . '.' . $key;
	}

	function isPresent($key, $locale = null)
	{
		$message = $this->lookupMessage($locale, $key);
		if (is_null($message))
		{
			return false;
		}
		elseif (preg_match('/^???.*???$/s', $message))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function getMessage($locale, $key, $args = array())
	{
		if (is_null($locale))
		{
			$locale = $this->defaultLocale;
		}

		$formatKey = $this->messageKey($locale, $key);
		if (isset($this->formats[$formatKey]))
		{
			$format = $this->formats[$formatKey];
		}
		// lookup the parsed message format
		else
		{
			$formatString = $this->lookupMessage($locale, $key);

			if (is_null($formatString))
			{
				if ($this->returnNull)
				{
					return null;
				}
				else
				{
					return '???' . $formatKey . '???';
				}
			}

			$format = preg_split('/{([0-9]+)}/', $formatString, -1, PREG_SPLIT_DELIM_CAPTURE);
			$this->formats[$formatKey] = $format;
		}

		// do the parametric replacement
		$result = '';
		foreach ($format as $i => $chunk)
		{
			if ($i % 2 == 0)
			{
				$result .= $chunk;
			}
			elseif (isset($args[$chunk]))
			{
				$result .= $args[$chunk];
			}
		}

		return $result;
	}
}
?>
