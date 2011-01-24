<?php
/* $Id: SaxParser.php 220 2005-06-23 19:38:30Z mojavelinux $
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
class SaxParser
{
	var $parser = null;

	/**
	 * NOTE: _handler is a little tmp parameter to make sure the xml_set_object uses pass-by-reference
	 */
	var $_handler = null;

	/**
	 * Number of characters to read for the input stream at a time while parsing xml document.
	 * This is for internal use only and is not available to the actuall implementation.
	 * @var int
	 * @access private
	 */
	var $_buffer = 4096;

	function SaxParser()
	{
		// sanity check for buffer
		if ($this->_buffer < 256)
		{
			$this->_buffer = 256;
		}

		/* @todo cannot reuse parser in PHP, so we might as well create it in the parse method
		$this->parser = xml_parser_create_ns();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_start_namespace_decl_handler($this->parser, '_startPrefixMapping');
		xml_set_end_namespace_decl_handler($this->parser, '_endPrefixMapping');
		xml_set_element_handler($this->parser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->parser, '_characters');
		xml_set_processing_instruction_handler($this->parser, '_processingInstruction');

		// we want to be able to reuse the parser, but we don't want to leave the parser
		// open, so we register a shutdown function for it to destroy itself
		register_shutdown_function(array(&$this, 'destroy'));
		*/
	}

	function isNamespaceAware()
	{
		return true;
	}

	/**
	 * NOTE: this method closes the input reader passed in.
	 */
	function parse(&$input, &$handler)
	{
		$this->parser = xml_parser_create_ns();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_start_namespace_decl_handler($this->parser, '_startPrefixMapping');
		xml_set_end_namespace_decl_handler($this->parser, '_endPrefixMapping');
		xml_set_element_handler($this->parser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->parser, '_characters');
		xml_set_processing_instruction_handler($this->parser, '_processingInstruction');
		// a little reference trick
		$this->_handler =& $handler;
		xml_set_object($this->parser, $this->_handler);

		if ($input->ready())
		{
			$handler->startDocument();

			$data = '';
			$input->read($data);
			$input->close();
			if (!xml_parse($this->parser, $data, true))
			{
				throw_exception(new RootException('SAX Parse Error: ' . xml_error_string(xml_get_error_code($this->parser)) . ' on line ' . xml_get_current_line_number($this->parser)));
				return;
			}

			$handler->endDocument();
		}
		else
		{
			return false;
		}

		xml_parser_free($this->parser);
		return true;
	}

	function &getParser()
	{
		return $this->parser;
	}

	function setProperty($property, $value)
	{
		xml_parser_set_option($this->parser, $property, $value);	
	}

	function getProperty($property)
	{
		return xml_parser_set_option($this->parser, $property);
	}

	function destroy()
	{
		xml_parser_free($this->parser);
	}
}
?>
