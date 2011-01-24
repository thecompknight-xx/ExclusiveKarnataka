<?php
/* $Id: SaxDefaultHandler.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package horizon.xml.parsers
 * @author Dan Allen
 */
class SaxDefaultHandler
{
	/**
	 * Registered namespaces we are currently processing privately so that we can properly
	 * call startElement/endElement in a namespace aware fashion.  This field is not available
	 * to the actual implementation and thus it will have to keep its own hash.
	 * @var array
	 * @access private
	 */
	var $_namespaces = array();

	/**
	 * Receive notification of the beginning of the document.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions at the beginning of a document (such
	 * as allocating the root node of a tree or creating an output file).
	 * @return void
	 */
	function startDocument()
	{
		; // default implementation does nothing
	}
	
	/**
	 * Receive notification of the end of the document.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions at the end of a document (such as
	 * finalising a tree or closing an output file).
	 * @return void
	 */
	function endDocument()
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of the start of a Namespace mapping.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions at the start of each Namespace
	 * prefix scope (such as storing the prefix mapping).
	 * @param string $prefix
	 * @param string $namespaceURI
	 * @return void
	 */
	function startPrefixMapping($prefix, $namespaceURI)
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of the end of a Namespace mapping.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions at the end of each prefix mapping.
	 * @param string $prefix
	 * @return void
	 */
	function endPrefixMapping($prefix)
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of the start of an element.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions at the start of each element (such
	 * as allocating a new tree node or writing output to a file).
	 * @param string $namespaceURI
	 * @param string $localName
	 * @param string $qName
	 * @param array $attributes
	 * @return void
	 */
	function startElement($namespaceURI, $localName, $qName, $attributes)
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of the end of an element.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions at the end of each element (such as
	 * finalising a tree node or writing output to a file).
	 * @param string $namespaceURI
	 * @param string $localName
	 * @param string $qName
	 * @return void
	 */
	function endElement($namespaceURI, $localName, $qName)
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of character data inside an element.
	 *
	 * By default, do nothing. Application writers may override this method to
	 * take specific actions for each chunk of character data (such as adding
	 * the data to a node or buffer, or printing it to a file).
	 * @param string $data
	 * @return void
	 */
	function characters($data)
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of ignorable whitespace in element content.
	 *
	 * By default, do nothing. Application writers may override this method to
	 * take specific actions for each chunk of ignorable whitespace (such as
	 * adding data to a node or buffer, or printing it to a file).
	 * @param string $data Whitespace characters
	 * @return void
	 */
	function ignorableWhitespace($data)
	{
		; // default implementation does nothing
	}

	/**
	 * Receive notification of a processing instruction.
	 *
	 * By default, do nothing. Application writers may override this method in
	 * a subclass to take specific actions for each processing instruction,
	 * such as setting status variables or invoking other methods.
	 * @param string $target
	 * @param string $data
	 * @return void
	 */
	function processingInstruction($target, $data)
	{
		; // default implementation does nothing
	}

	/**
	 * An adapter method between PHPs implementation of the start_namespace_decl_handler and the public
	 * startPrefixMapping method recommended by the SAX2 API
	 * @access private
	 * @return void
	 */
	function _startPrefixMapping($parser, $prefix, $namespaceURI)
	{
		$this->_namespaces[$prefix] = $namespaceURI;
		$this->startPrefixMapping($prefix, $namespaceURI);
	}

	/**
	 * An adapter method between PHPs implementation of the end_namespace_decl_handler and the public
	 * endPrefixMapping method recommended by the SAX2 API
	 * @access private
	 * @return void
	 */
	function _endPrefixMapping($parser, $prefix)
	{
		unset($this->_namespaces[$prefix]);
		$this->endPrefixMapping($prefix);
	}

	/**
	 * An adapter method between PHPs implementation of the start_element_handler and the public
	 * startElement method recommended by the SAX2 API
	 * @access private
	 * @return void
	 */
	function _startElement($parser, $name, $attributes)
	{
		$lastColon = strrpos($name, ':');
		if ($lastColon !== false)
		{
			$namespaceURI = substr($name, 0, $lastColon);
			$localName = substr($name, $lastColon + 1);
			$qName = array_search($namespaceURI, $this->_namespaces) . ':' . $localName;
		}
		else
		{
			$namespaceURI = '';
			$localName = '';
			$qName = $name;
		}

		$this->startElement($namespaceURI, $localName, $qName, $attributes);
	}

	/**
	 * An adapter method between PHPs implementation of the end_element_handler and the public
	 * endElement method recommended by the SAX2 API
	 * @access private
	 * @return void
	 */
	function _endElement($parser, $name)
	{
		$lastColon = strrpos($name, ':');
		if ($lastColon !== false)
		{
			$namespaceURI = substr($name, 0, $lastColon);
			$localName = substr($name, $lastColon + 1);
			$qName = array_search($namespaceURI, $this->_namespaces) . ':' . $localName;
		}
		else
		{
			$namespaceURI = '';
			$localName = $name;
			$qName = $name;
		}

		$this->endElement($namespaceURI, $localName, $qName);
	}

	/**
	 * An adapter method between PHPs implementation of the cdata_handler and the public
	 * characters method recommended by the SAX2 API
	 * @access private
	 * @return void
	 */
	function _characters($parser, $data)
	{
        preg_match('/^(\s*)(.*?)(\s*)$/s', $data, $matches);
		if (strlen($matches[1]) > 0)
		{
			$this->ignorableWhitespace($matches[1]);
		}

		if (strlen($matches[2]) > 0)
		{
			$this->characters($matches[2]);
		}

		if (strlen($matches[3]) > 0)
		{
			$this->ignorableWhitespace($matches[3]);
		}
	}

	/**
	 * An adapter method between PHPs implementation of the processing_instruction_handler and the public
	 * processingInstruction method recommended by the SAX2 API
	 * @access private
	 * @return void
	 */
	function _processInstruction($parser, $target, $data)
	{
		$this->processingInstruction($target, $data);
	}
}
?>
