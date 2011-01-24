<?php
/* $Id: PhaseParser.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.util.StringUtils');
import('phase.PhaseException');
import('phase.compiler.TagLibraries');
import('horizon.util.logging.Logger');

/**
 * @package phase.compiler
 * @author Dan Allen
 *
 * TODO: THROW ERRORS!!! including throwing an error if the custom tab library cannot be found
 * TODO try to eliminate some of the gross space left behind by the parsing
 * TODO: whitespace is still an important issue since it is so bad in jsp, I want it to be good in this project
 * FIXME: using php tags inside an attribute is not working (rtexpr)
 */
class PhaseParser
{
	var $reader = null;

	var $mark = null;

	var $tagStack = null;

	var $varTagNumbers = null;

	var $tagFunctions = null;

	var $namespace = null;

	var $libraries = null;

	var $ctxt = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		return Logger::getLogger('phase.compiler.PhaseParser');
	}

	function PhaseParser(&$reader, &$ctxt)
	{
		$this->reader =& $reader;
		$this->ctxt =& $ctxt;
		$this->output = '';
		$this->tagStack = array();
		$this->start = $reader->mark();
		$this->tagVarNumbers = array();
		$this->tagFunctions = array();
		$this->namespace = $this->generateNamespace($ctxt->getPhaseFile());
		$this->libraries =& new TagLibraries();
	}

	/**
	 * The main entry for the parse, which will read through the template
	 * data and handle the tags in a hierarchial manner.  It will return
	 * a compiled template string
	 *
	 * @return string
	 */
	function parse(&$reader, &$ctxt)
	{
		$parser =& new PhaseParser($reader, $ctxt);
		$output = '';
		
		// NOTE: this method provides an easy way to setup the tag libraries
		$parser->loadTagLibraries();

		while ($reader->hasMoreInput())
		{
			$output .= $parser->parseElements();
		}

		// tack on the tag functions, wrapping them in the namespace
		// for this phase context
		if (count($parser->tagFunctions))
		{
			$output .= 
			'<?php' . "\n" .
			'class ' . $parser->namespace . ' {' . "\n" .
			implode("\n", array_reverse($parser->tagFunctions)) . "\n" .
			'}' . "\n" .
			'?>' . "\n";	
		}

		// merge sections where php tags have become adjacent (no need for them)
		$output = preg_replace('; *\?><\?php *;', ' ', $output);

		return $output;
	}

	function parseElements()
	{
		$this->start = $this->reader->mark();
		if ($this->reader->matches('<%@'))
		{
			$output = $this->parseDirective();
		}
		elseif ($this->reader->matches('<%--'))
		{
			$output = $this->parseComment();
		}
		elseif ($this->reader->matches('<?'))
		{
			$output = $this->parseScriptlet();
		}
		elseif ($this->reader->matches('<jsp:'))
		{
			$output = '<?php ' . $this->parseAction() . '?>';
		}
		elseif ($this->parseCustomTag($output))
		{
			$output = '<?php ' . $output . ' ?>';
		}
		else
		{
			$output = $this->parseTemplateText();
		}

		return $output;
	}

	/**
	 * Parse a directive section
	 */
	function parseDirective()
	{
		$this->start = $this->reader->mark();
		// skip over directive
		$stop = $this->reader->skipUntil('%>');
		if (is_null($stop))
		{
			throw_exception(new PhaseException('Directive not properly ended.'));
			return;
		}

		$this->reader->skipSpaces();
		return '';
	}

	/**
	 * Parse a comment section
	 */
	function parseComment()
	{
		$this->start = $this->reader->mark();
		// skip over the comment
		$stop = $this->reader->skipUntil('--%>');
		if (is_null($stop))
		{
			throw_exception(new PhaseException('Comment not properly ended.'));
			return;
		}

		$this->reader->skipSpaces();
		return '';
	}

	/**
	 * Parse a scriptlet
	 */
	function parseScriptlet()
	{
		$print = false;

		// skipping the extra 'php' bit if present
		if ($this->reader->matches('php '))
		{
			// do nothing, already skipped ahead
		}
		// short output tag, prepend an "echo" statement to output
		elseif ($this->reader->matches('='))
		{
			$print = true;
		}

		$this->start = $this->reader->mark();
		$stop = $this->reader->skipUntil('?>');
		if (is_null($stop))
		{
			throw_exception(new PhaseException('Scriptlet not properly ended'));
			return;
		}

		return '<?php ' . ($print ? 'echo ' : '') . trim($this->reader->getChars($this->start, $stop)) . '?>';
	}

	function parseAction()
	{
		$start = $this->reader->mark();
		if ($this->reader->matches('useBean'))
		{
			$attributes = $this->parseAttributes();
			$this->reader->skipSpaces();
			if ($this->reader->matches('/>'))
			{
				return '$' . $attributes['id'] . ' =& $pageContext->getAttribute(' . StringUtils::quote($attributes['id']) . ', ' . StringUtils::quote($attributes['scope']) . ');';		
			}
		}
	}

	/**
	 * Consume the plain old template text, trimming empty regions and
	 * optionally running it through the EL evaluator.
	 */
	function parseTemplateText()
	{
		$buf = '';
		// we only need this if we are going to use JSP tags for wrapping
		// scriptlet blocks
		//if ($this->reader->matches('<' . '\\%'))
		//{
		//	$buf .= '<' . '%';
		//}

		$buf .= $this->reader->nextContent();

		// don't output meaningless whitespace
		if (trim($buf) == '')
		{
			return '';
		}
		elseif ($this->ctxt->options->isElIgnored() || strpos($buf, '${') === false)
		{
			return $buf;
		}
		else
		{
			return '<?php echo $pageContext->evaluateTemplateText(' . StringUtils::quote($buf) . ');?>';
		}
	}

	function parseCustomTag(&$output)
	{
		if ($this->reader->peekChar() != '<')
		{
			return false;
		}

		$this->reader->nextChar();
		$tagname = $this->reader->parseToken(false);

		// if no namespace prefix is present, this cannot be a custom tag
		if (strpos($tagname, ':') === false)
		{
			$this->reader->reset($this->start);
			return false;
		}

		list($prefix, $shortTagName) = explode(':', $tagname);

		// see if this is a known tag
		if (!$this->libraries->isDefinedTag($prefix, $shortTagName))
		{
			$this->reader->reset($this->start);
			return false;
		}

		$attributes = $this->parseAttributes();
		$className = $this->getClassNameForTag($prefix, $shortTagName);
		$varName = $this->createTagVarName($prefix, $shortTagName);
		// don't need this since we use class namespaces
		//$funcName = '_phase_tag_' . $varName;
		$funcName = $varName;
		$function = 'function ' . $funcName . '(&$parent, &$pageContext) {' . "\n";
		$function .= "\t" . '$_phase_tag_pool =& TagHandlerPool::getInstance();' . "\n";
		$function .= "\t" . '$_phase_tag =& $_phase_tag_pool->borrowTag(' . StringUtils::quote($className) . ');' . "\n";
		$function .= "\t" . '$_phase_tag->setPageContext($pageContext);' . "\n";
		$function .= "\t" . '$_phase_tag->setParent($parent);' . "\n";
		if (count($this->tagStack))
		{
			$parent = '$_phase_tag';
		}
		else
		{
			$parent = 'ref(null)';
		}

		array_push($this->tagStack, $varName);

		$output .= 'if (' . $this->namespace . '::' . $funcName . '(' . $parent . ', $pageContext)) { return true; }';

		foreach ($attributes as $name => $value)
		{
			$method = 'set' . ucfirst($name);
			// make sure this is a valid attribute for this tag
			// NOTE: somewhat dirty, but at least it works...could use MethodUtils#invokeMethod()
			$function .= "\t" . 'if (!method_exists($_phase_tag, \'' . $method . '\')) { throw_exception(new PhaseException(\'Attribute "' . $name . '" invalid for tag "' . $tagname . '"\')); return; }' . "\n";
			$function .= "\t" . '$_phase_tag->' . $method . '(' . StringUtils::quote($value) . ');' . "\n";
		}

		$this->reader->skipSpaces();

		$function .= "\t" . '$_phase_flag_start_tag = $_phase_tag->doStartTag();' . "\n";
		// check for short tag
		if ($this->reader->matches('/>'))
		{
			$function .= "\t" . 'if ($_phase_tag->doEndTag() == c(\'Tag::SKIP_PAGE\')) { return true; }' . "\n";
			$function .= "\t" . '$_phase_tag_pool->returnTag(' . StringUtils::quote($className) . ', $_phase_tag);' . "\n";
			$function .= "\t" . 'return false;' . "\n";
			$function .= '}' . "\n";

			array_pop($this->tagStack);
			array_push($this->tagFunctions, $function);
			return true;
		}

		if (!$this->reader->matches('>'))
		{
			throw_exception(new PhaseException($tagname . ' not properly ended'));
			return;
		}

		$function .= "\t" . 'if ($_phase_flag_start_tag != c(\'Tag::SKIP_BODY\')) {' . "\n";
		$tagClass =& Clazz::forName($className);
		$hasBodyTagSupport = $tagClass->isAssignableFrom('phase.tagext.BodyTagSupport');
		if ($hasBodyTagSupport)
		{
			$function .= "\t\t" . 'if ($_phase_flag_start_tag != c(\'Tag::EVAL_BODY_INCLUDE\')) { ob_start(); }' . "\n";
		}

		$function .= "\t\t" . 'do {' . "\n";
		$function .= '?>' . $this->parseBody($tagname) . '<?php' . "\n";
		$function .= "\t\t" . '} while ($_phase_tag->doAfterBody() == c(\'Tag::EVAL_BODY_AGAIN\'));' . "\n";
		if ($hasBodyTagSupport)
		{
			$function .= "\t\t" . 'if ($_phase_flag_start_tag != c(\'Tag::EVAL_BODY_INCLUDE\')) { $_phase_tag->setBodyContent(ob_get_clean()); }' . "\n";
		}

		$function .= "\t" . '}' . "\n";
		$function .= "\t" . 'if ($_phase_tag->doEndTag() == c(\'Tag::SKIP_PAGE\')) { return true; }' . "\n";
		$function .= "\t" . '$_phase_tag_pool->returnTag(' . StringUtils::quote($className) . ', $_phase_tag);' . "\n";
		$function .= "\t" . 'return false;' . "\n";
		$function .= '}' . "\n";

		array_pop($this->tagStack);
		array_push($this->tagFunctions, $function);
		return true;
	}

	/**
	 * Read the stream until the end tag is found
	 * which matches the start tag passed in.
	 *
	 * @return void
	 */
	function parseBody($tagname)
	{
		$output = '';
		while ($this->reader->hasMoreInput())
		{
			if ($this->reader->matchesEndTag($tagname))
			{
				return $output;
			}

			$output .= $this->parseElements();
		}

		// @todo throw error, never found end tag, where???
	}

	/**
	 * @return array
	 */
	function parseAttributes()
	{
		$attributes = array();
		$this->reader->skipSpaces();
		while ($this->parseAttribute($attributes))
		{
			$this->reader->skipSpaces();
		}

		return $attributes;
	}

	function parseAttribute(&$attributes)
	{
		$name = $this->parseName();
		if (is_null($name))
		{
			return false;
		}

		$equals = $this->reader->nextChar();
		if ($equals != '=')
		{
			// @todo throw error
		}

		$quote = $this->reader->nextChar();
		if ($quote != '\'' && $quote != '"')
		{
			// @todo throw error
		}

		// get the attribute value and add it to our name/value pairs
		$attributeValue = $this->parseAttributeValue($quote);
		$attributes[$name] = $attributeValue;
		return true;
	}

	// currently this allows for case-folding (upper case and lower case alike)
	function parseName()
	{
		$buf = '';	
		$ch = $this->reader->peekChar();
		$chOrd = ord($ch);
		// [A-Za-z:_]
		if (($chOrd >= 66 && $chOrd <= 90) || ($chOrd >= 97 && $chOrd <= 122) || $chOrd == 58 || $chOrd == 95)
		{
			$buf .= $ch;
			$this->reader->nextChar();
			$ch = $this->reader->peekChar();
			$chOrd = ord($ch);
			// [A-Za-z:_-\.]
			while (($chOrd >= 66 && $chOrd <= 90) || ($chOrd >= 97 && $chOrd <= 122) || ($chOrd >= 48 && $chOrd <= 57) || $chOrd == 58 || $chOrd == 95 || $chOrd == 46 || $chOrd == 45)
			{
				$buf .= $ch;
				$this->reader->nextChar();
				$ch = $this->reader->peekChar();
				$chOrd = ord($ch);
			}

			return $buf;
		}

		return null;
	}

	function parseAttributeValue($quote)
	{
		$start = $this->reader->mark();
		// skip until we find a non-escaped instance of our quote, only caring about
		// escapes involving our start quote an none other
		$stop = $this->reader->skipUntil($quote, true, array($quote));
		if (is_null($stop))
		{
			// @todo throw error
		}

		// get the value of the attribute, stripping any escaped slashes
		// NOTE: interesting here is that it will strip slashes that didn't
		// originally perform any escaping function, but JSP does it this way
		$ret = stripslashes($this->reader->getChars($start, $stop));

		// @todo handle putting back php tags in attribute
		return $ret;
	}

	function createTagVarName($prefix, $shortTagName)
	{
		$qName = $prefix . ':' . $shortTagName;
		$varName = $prefix . '_' . $shortTagName . '_';
		if (isset($this->tagVarNumbers[$qName]))
		{
			$i =& $this->tagVarNumbers[$qName];
			$varName .= ++$i;
		}
		else
		{
			$this->tagVarNumbers[$qName] = 0;
			$varName .= 0;
		}

		return $varName;
	}

	/**
	 * This is kind of a hack, we just want to generate a classname from the
	 * pieces we have laying around.
	 *
	 * @return string The classname corresponding to this custom tag
	 * @throws PhaseException If the custom tag class cannot be created
	 */
	function getClassNameForTag($prefix, $shortTagName)
	{
		// NOTE: could we add a convenience method to make this easier?
		$tli = $this->libraries->getTagLibInfo($prefix);
		$ti =& $tli->getTag($shortTagName);
		if (is_null($ti))
		{
			throw_exception(new PhaseException('Unable to location custom tag definition for ' . $prefix . ':' . $shortTagName));
			return null;
		}

		return $ti->getTagClass();
	}

	/**
	 * Rely on the TabLibraries class to location and process the *.tld files
	 * for the custom taglibs.
	 */
	function loadTagLibraries()
	{
		$this->libraries->processTlds($this->ctxt);
	}

	/**
	 * Generate a class name which will be used to wrap the generated
	 * functions for this phase file.  The class name acts as a namespace
	 * to prevent collision from generated functions from other files.
	 */
	function generateNamespace($uri)
	{
		// QUESTION: should we try to make shorter namespaces using humpback?
		$className = 'phase_';

		$info = pathinfo(substr($uri, 1));
		$dirname = ltrim($info['dirname'], '.');
		if (strlen($dirname) > 0) {
			$className .= str_replace('/', '_', $dirname) . '_';
		}

		$className .= basename($uri, '.' . $info['extension']);
		// make sure we don't have an invalid name
		$className = preg_replace('/[^A-Za-z0-9_]/', '_', $className);
		return $className;
	}
}
?>
