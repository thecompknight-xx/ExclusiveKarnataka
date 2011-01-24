<?php
/* $Id: TagLibraries.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.xml.digester.Digester');
import('horizon.util.logging.Logger');

/**
 * A container for all tag libraries that have been imported.
 *
 * @package phase.compiler
 * @author Dan Allen
 */
class TagLibraries
{
	var $taglibInfos = array();

	var $digester = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('phase.compiler.TagLibraries');
		return $logger;
	}

	function addTagLibrary($prefix, &$tli)
	{
		$this->taglibInfos[$prefix] =& $tli;
	}

	/**
	 * Determine if the tag provided exists, looking up by
	 * prefix and by shortName against the known tag libraries.
	 *
	 * @param string $prefix The namespace used on the tag element
	 * @param string $shortName The name of the tag
	 *
	 * @return boolean Whether or not the tag exists
	 */
	function isDefinedTag($prefix, $shortName)
	{
		if (isset($this->taglibInfos[$prefix]))
		{
			$tli =& $this->taglibInfos[$prefix];
			if ($tli->getTag($shortName) != null)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Don't return by reference since it should be read only
	 */
	function getTagLibInfo($prefix)
	{
		if (!isset($this->taglibInfos[$prefix]))
		{
			return null;
		}
		
		return $this->taglibInfos[$prefix];
	}

	/**
	 * @param string $tldPath A resource path relative to the web-application root
	 */
	function processTld(&$ctxt, $tldPath)
	{
		$log =& $this->getLog();
		if ($log->isLoggable('DEBUG')) {
			$log->debug('Processing tld library ' . $tldPath);
		}
		// try {
			$tli =& $this->digester->parse($ctxt->getResourceAsStream($tldPath));
			$this->addTagLibrary($tli->getPrefix(), $tli);
		// } catch (RootException $ex) {
		if ($e = catch_exception())
		{
			$log->error('Problem encountered while parsing tld: ' . $e->getMessage(), $e);
		}
	}

	function processTlds(&$ctxt)
	{
		// NOTE: originally digester was a local var...where should it be???
		$this->digester =& $this->initTldDigester();
		$tlds = $ctxt->getResourcePaths($ctxt->options->getTldResourceDir());
		foreach ($tlds as $tldPath)
		{
			$tldPathStr =& new String($tldPath);
			if ($tldPathStr->endsWith('.tld'))
			{
				$this->processTld($ctxt, $tldPath);
			}
		}
	}

	function &initTldDigester()
	{
		$digester =& new Digester();

		$digester->addObjectCreate('taglib', 'phase.compiler.TagLibraryInfo');

		$digester->addBeanPropertySetter('taglib/tlib-version', 'version');

		$digester->addBeanPropertySetter('taglib/short-name', 'prefix');

		$digester->addBeanPropertySetter('taglib/uri');

		$digester->addObjectCreate('taglib/tag', 'phase.compiler.TagInfo');

		$digester->addBeanPropertySetter('taglib/tag/name');

		$digester->addBeanPropertySetter('taglib/tag/tag-class', 'tagClass');

		$digester->addSetNext('taglib/tag', 'addTag', 'phase.compiler.TagInfo');

		return $digester;	
	}
}
?>
