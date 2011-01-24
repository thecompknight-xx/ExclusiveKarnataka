<?php
/* $Id: Rule.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package horizon.xml.digester
 * @abstract
 * @author Dan Allen
 */
class Rule
{
	/**
	 * The Digester with which this Rule is associated.
	 * @var Digester
	 */
	var $digester = null;

	/**
	 * The namespace URI for which this Rule is relevant, if any.
	 * @var string
	 */
	var $namespaceURI = null;

	/**
	 * Return the Digester with which this Rule is associated.
	 * @return Digester
	 */
	function getDigester()
	{
		return $this->digester;
	}

	/**
	 * Set the {@link Digester} with which this {@link Rule} is associated.
	 * @param Digester $digester
	 * @return void
	 */
	function setDigester(&$digester)
	{
		$this->digester =& $digester;
	}

	/**
	 * Return the namespace URI for which this Rule is relevant, if any.
	 * @return string
	 */
	function getNamespaceURI()
	{
		return $this->namespaceURI;
	}

	/**
	 * Set the namespace URI for which this Rule is relevant, if any.
	 * @param string $namespaceURI
	 * @return void
	 */
	function setNamespaceURI($namespaceURI)
	{
		$this->namespaceURI = $namespaceURI;
	}

	/**
	 * This method is called when the beginning of a matching XML element is encountered.
	 * @param string $namespace
	 * @param string $name
	 * @param array $attributes
	 * @return void
	 */
	function begin($namespace, $name, $attributes)
	{
		; // default implementation does nothing
	}

	/**
	 * This method is called when the body of a matching XML element is encountered.
	 * @param string $namespace
	 * @param string $name
	 * @param string $text
	 * @return void
	 */
	function body($namespace, $name, $text)
	{
		; // default implementation does nothing
	}

	/**
	 * This method is called when the end of a matching XML element is encountered.
	 * @param string $namespace
	 * @param string $name
	 * @return void
	 */
	function end($namespace, $name)
	{
		; // default implementation does nothing
	}

	/**
	 * This method is called after all parsing methods have been called, to
	 * allow Rules to remove temporary data.
	 * @return void
	 */
	function finish()
	{
		; // default implementation does nothing
	}
}
?>
