<?php
/* $Id: Digester.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.xml.parsers.SaxParser');
import('horizon.xml.parsers.SaxDefaultHandler');
import('horizon.xml.digester.RulesBase');
import('horizon.xml.digester.SetPropertyRule');
import('horizon.xml.digester.SetPropertiesRule');
import('horizon.xml.digester.SetNextRule');
import('horizon.xml.digester.ObjectCreateRule');
import('horizon.xml.digester.CallMethodRule');
import('horizon.xml.digester.CallParamRule');
import('horizon.xml.digester.BeanPropertySetterRule');
import('horizon.util.logging.Logger');

/**
 * A <b>Digester</b> processes an XML input reader by matching a
 * series of element nesting patterns to execute Rules that have been added
 * prior to the start of parsing.
 *
 * The main difference between this class and the jakarta commons-digester
 * implementation is that the Handlers for SAX events have been merged into a
 * single class called {@link SaxDefaultHandler} for simplicity and
 * the XmlReader/Parser have been merged into a {@link SaxParser}
 * class.  Mainly this was done for the sake of time, but it seems to work
 * great and the abstraction is still quite good.
 *
 * NOTE: Several of the rules make use of BeanUtils to set properties on
 * objects at the top of the digester stack.  Unfortunately, since PHP is
 * weakly typed, there is no way to know what type to use to convert the xml
 * string value.  Therefore, any beans that are being populated with the
 * digester component must do the necessary convertion of string to the target
 * type in the setXXX() method.  The only possible way around this would be to
 * have BeanUtils/MethodUtils read the javadoc in the PHP class in order to
 * know what type to use for conversion, but that will be significantly slower.
 *
 * @package horizon.xml.digester
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig McClanahan, Scott Sanders, Jean-Francois Arcand, John C. Wildenauer
 *
 * TODO: use ArrayStack for the peek/pop/push methods
 * TODO: better use of Iterators, exception handling and logging
 */
class Digester extends SaxDefaultHandler
{
	/**
	 * The body text of the current element.
	 * @var string
	 */
	var $bodyText = '';

	/**
	 * The stack of body text string buffers for surrounding elements.
	 * @var array
	 */
	var $bodyTexts = array();

	/**
	 * Has this Digester been configured yet.
	 * @var boolean
	 */
	var $configured = false;	

	/**
	 * The application-supplied handler that is notified when a parse event occurs
	 * @var SaxDefaultHandler
	 */
	var $handler = null;

	/**
	 * The current match pattern for nested element processing.
	 * @var string
	 */
	var $match = '';

	/**
	 * Do we want a "namespace aware" parser.
	 * @var boolean
	 */
	var $namespaceAware = false;		

	/**
	 * Registered namespaces we are currently processing.
	 * @var array
	 */
	var $namespaces = array();

	/**
	 * The parameters stack being utilized by CallMethodRule and
	 * CallParamRule rules
	 * @var array
	 */
	var $params = array();

	/**
	 * The SaxParser we will use to parse the input stream.
	 * @var SaxParser
	 */
	var $parser = null;

	/**
	 * The "root" element of the stack (in other words, the last object that was popped.
	 * @var object
	 */
	var $root = null;

	/**
	 * The Rules implementation containing our collection of Rule instances and
	 * associated matching policy.
	 * @var Rules
	 */
	var $rules = null;

	/**
	 * The object stack being constructed.
	 * @var array
	 */
	var $stack = array();

	/**
	 * Do we want to use a validating parser.
	 * @var boolean
	 */
	var $validating = false;

	/**
	 * The DTD against which this digester should validate.
	 * @var string
	 */
	var $dtdURL = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('horizon.xml.digester.Digester');
		return $logger;
	}

	/**
	 * NOTE: can't pass by reference here and have it be optional unless we do the array trick
	 */
	function Digester($parser = null)
	{
		$this->parser =& $parser;
	}

	/**
	 * Return the currently mapped namespace URI for the specified prefix, if
	 * any; otherwise return <kbd>null</kbd>.  These mappings come and go
	 * dynamically as the document is parsed.
	 * @param string $prefix
	 * @return string
	 */
	function findNamespaceURI($prefix)
	{
		if (isset($this->namespaces[$prefix]))
		{
			return $this->namespaces[$prefix];
		}

		return null;
	}

	/**
	 * Return the current depth of the element stack.
	 * @return int
	 */
	function getCount()
	{
		return count($this->stack);
	}

	/**
	 * Return the name of the XML element that is currently being processed.
	 * @return string
	 */
	function getCurrentElementName()
	{
		$elementName = $this->match;
		$lastSlash = strrpos($elementName, '/');
		if ($lastSlash !== false)
		{
			$elementName = substr($elementName, $lastSlash + 1);
		}

		return $elementName;
	}

	/**
	 * Get the SAX handler for this Digester.
	 * @return SaxDefaultHandler
	 */
	function &getHandler()
	{
		return $this->handler;
	}

	/**
	 * Set the SAX handler for this Digester.
	 * @param SaxDefaultHandler
	 * @return void
	 */
	function setHandler(&$handler)
	{
		$this->handler =& $handler;
	}

	/**
	 * Return the current rule match path.
	 * @return string
	 */
	function getMatch()
	{
		return $this->match;
	}

	/**
	 * Return the "namespace aware" flag for parsers we create.
	 * @return boolean
	 */
	function getNamespaceAware()
	{
		return $this->namespaceAware;
	}

	/**
	 * Set the "namespace aware" flag for parsers we create.
	 * @param boolean $namespaceAware
	 * @return void
	 */
	function setNamespaceAware($namespaceAware)
	{
		$this->namespaceAware = $namespaceAware;
	}

	/**
	 * Return the namespace URI that will be applied to all subsequently added
	 * {@link Rule} objects.
	 *
	 * @return string
	 */
	function getRuleNamespaceURI()
	{
		$rules =& $this->getRules();
		return $rules->getNamespaceURI();
	}

	/**
	 * Set the namespace URI that will be applied to all subsequently added
	 * {@link Rule} objects.
	 *
	 * @param string $ruleNamespaceURI Namespace URI that must match on all
	 *  subsequently added rules, or <kbd>null</kbd> for matching regardless
	 *  of the current namespace URI
	 * @return void
	 */
	function setRuleNamespaceURI($ruleNamespaceURI)
	{
		$rules =& $this->getRules();
		$rules->setNamespaceURI($ruleNamespaceURI);
	}

	/**
	 * Return the SaxParser we will use to parse the input reader.  If there is
	 * a problem creating the parser, return <kbd>null</kbd>.
	 * @return SaxParser
	 */
	function &getParser()
	{
		if (is_null($this->parser))
		{
			$this->parser =& new SaxParser();
		}

		return $this->parser;
	}

	/**
	 * Return the current value of the specified property for the underlying
	 * {@link SaxParser} implementation.
	 *
	 * @param string $property
	 * @return boolean
	 */
	function getProperty($property)
	{
		$parser =& $this->getParser();
		return $parser->getProperty($property);
	}

	/**
	 * Set the current value of the specified property for the underlying
	 * {@link XMLReader} implementation.
	 * @param string $property
	 * @param boolean $value
	 * @return void
	 */
	function setProperty($property, $value)
	{
		$parser =& $this->getParser();
		$parser->setProperty($property, $value);
	}

	/**
	 * Return the Rules implementation object containing our rules collection
	 * and associated matching policy.
	 * @return Rules
	 */
	function &getRules()
	{
		if (is_null($this->rules))
		{
			$this->rules =& new RulesBase();
			$this->rules->setDigester($this);
		}

		return $this->rules;
	}

	/**
	 * Set the Rules implementation object containing our rules collection and associated matching policy.
	 * @param Rules $rules
	 * @return void
	 */
	function setRules(&$rules)
	{
		$this->rules =& $rules;
		$this->rules->setDigester($this);
	}

	/**
	 * Return the validating parser flag.
	 * @return boolean
	 */
	function getValidating()
	{
		return $this->validating;
	}

	/**
	 * Set the validating parser flag.
	 * @param boolean $validating
	 * @return void
	 */
	function setValidating($validating)
	{
		$this->validating = $validating;
	}

	/**
	 * Process notification of character data received from the body of an XML
	 * element.
	 * @param string $buffer
	 * @return void
	 */
	function characters($buffer)
	{
		$this->bodyText .= $buffer;
	}

	/**
	 * Process notification of the end of the document being reached.
	 * @return void
	 */
	function endDocument()
	{
		$log =& $this->getLog();
		$log->trace('Processing end of document');

		while ($this->getCount() > 1)
		{
			$this->pop();
		}

		// fire "finish" events for all defined rules
		$rules = $this->getRules();
		$rules = $rules->rules();
		for ($i = 0; $i < count($rules); $i++)
		{
			$rules[$i]->finish();
		}

		$this->clear();
	}

	/**
	 * Process notification of the end of an XML element being reached.
	 * @param string $namespaceURI
	 * @param string $localName
	 * @param string $qName
	 * @return void
	 */
	function endElement($namespaceURI, $localName, $qName)
	{
		$log =& $this->getLog();

		$name = $localName;
		if (is_null($name) || strlen($name) < 1)
		{
			$name = $qName;
		}

		$log->trace('Processing end element: ' . $name);

		// fire "body" events for all relevant rules
		$rules = $this->getRules();
		$rules = $rules->match($namespaceURI, $this->match);
		if (!is_null($rules) && count($rules) > 0)
		{
			for ($i = 0; $i < count($rules); $i++)
			{
				$rules[$i]->body($namespaceURI, $name, $this->bodyText);
			}
		}

		$this->bodyText = array_pop($this->bodyTexts);
		
		// fire "end" events for all relevant rules
		if (!is_null($rules) && count($rules) > 0)
		{
			for ($j = count($rules) - 1; $j >= 0; $j--)
			{
				$rules[$j]->end($namespaceURI, $name);
			}
		}

		// recover the previous match expression
		$slashPos = strrpos($this->match, '/');
		if ($slashPos !== false)
		{
			$this->match = substr($this->match, 0, $slashPos);
		}
		else
		{
			$this->match = '';
		}
	}

	/**
	 * Process notification that a namespace prefix is going out of scope.
	 * @param string $prefix
	 * @return void
	 */
	function endPrefixMapping($prefix)
	{
		unset($this->namespaces[$prefix]);
	}

	/**
	 * Process notification of ignorable whitespace received from the body of an XML element.
	 * @param string $data
	 * @return void
	 */
	function ignorableWhitespace($data)
	{
		; // no action taken
	}

	/**
	 * Process notification of a processing instruction that was encountered.
	 * @param string $target
	 * @param string $data
	 * @return void
	 */
	function processingInstruction($target, $data)
	{
		; // no action taken
	}

	/**
	 * Process notification of the beginning of the document being reached.
	 * @return void
	 */
	function startDocument()
	{
		$this->configure();
	}

	/**
	 * Process notification of the start of an XML element being reached.
	 * @param string $namespaceURI
	 * @param string $localName
	 * @param string $qName
	 * @param array $attributes
	 * @return void
	 */
	function startElement($namespaceURI, $localName, $qName, $attributes)
	{
		$log =& $this->getLog();

		// save the body text accumulated for our surrounding element
		array_push($this->bodyTexts, $this->bodyText);
		$this->bodyText = '';

		$name = $localName;
		if (is_null($name) || strlen($name) < 1)
		{
			$name = $qName;
		}

		$log->trace('Processing start element: ' . $name);

		if (strlen($this->match) > 0)
		{
			$this->match .= '/';
		}

		$this->match .= $name;

		// fire "begin" events for all relevant rules
		$rules = $this->getRules();
		$rules = $rules->match($namespaceURI, $this->match);
		if (!is_null($rules) || count($rules) > 0)
		{
			for ($i = 0; $i < count($rules); $i++)
			{
				$rules[$i]->begin($namespaceURI, $name, $attributes);
			}
		}
	}

	/**
	 * Process notification that a namespace prefix is coming in to scope.
	 * @param string $prefix
	 * @param string $namespaceURI
	 * @return void
	 */
	function startPrefixMapping($prefix, $namespaceURI)
	{
		$this->namespaces[$prefix] = $namespaceURI;
	}

	/**
	 * Parse the content of the specified input reader using this Digester.
	 * Returns the root element from the object stack (if any).
	 * @param Reader $input
	 * @return object
	 */
	function parse(&$input)
	{
		$log =& $this->getLog();

		$this->configure();
		$parser =& $this->getParser();

		if ($log->isLoggable('DEBUG')) {
			$log->debug('Parsing input with digester instance using ' . get_class($parser) . ' parser');
		}

		$parser->parse($input, $this);
		return $this->root;
	}

	/**
	 * Register a new Rule matching the specified pattern.  This method sets
	 * the {@link Digester} property on the rule.
	 *
	 * @param string $pattern
	 * @param Rule $rule
	 * @return void
	 */
	function addRule($pattern, &$rule)
	{
		$rule->setDigester($this);
		$rules =& $this->getRules();
		$rules->add($pattern, $rule);
	}

	/**
	 * Register a set of Rule instances defined in a RuleSet.
	 * @param RuleSet $ruleSet
	 * @return void
	 */
	function addRuleSet(&$ruleSet)
	{
		$oldNamespaceURI = $this->getRuleNamespaceURI();
		$newNamespaceURI = $ruleSet->getNamespaceURI();
		$this->setRuleNamespaceURI($newNamespaceURI);
		$ruleSet->addRuleInstances($this);
		$this->setRuleNamespaceURI($oldNamespaceURI);
	}

	/**
	 * Add an "call method" rule for the specified parameters.
	 * @param string $pattern
	 * @param string $methodName
	 * @param int $paramCount Optional number of parameters
	 * @return void
	 */
	function addCallMethod($pattern, $methodName, $paramCount = 0)
	{
		$this->addRule($pattern, new CallMethodRule($methodName, $paramCount));
	}

	/**
	 * Add a "call parameter" rule for the specified parameters.
	 * @param string $pattern Element matching pattern
	 * @param string $paramIndex Zero-relative parameter index to set (from the body of this element)
	 * @param string $attributeName Optional attribute whose value is used as the parameter value
	 * @param int $stackIndex Optional set the call parameter to the stackIndex'th object down the stack,
	 *  where 0 is the top of the stack, 1 the next element down and so on
	 * @return void
	 */
	function addCallParam($pattern, $paramIndex, $attributeName = null, $stackIndex = -1)
	{
		$this->addRule($pattern, new CallParamRule($paramIndex, $attributeName, $stackIndex));
	}

	/**
	 * Add an "object create" rule for the specified parameters.
	 * @param string $pattern Element matching pattern
	 * @param string $className Optional class name to be created
	 * @param string $attributeName Optional attribute name the overrides the default class name
	 * @return void
	 */
	function addObjectCreate($pattern, $className, $attributeName = null)
	{
		$this->addRule($pattern, new ObjectCreateRule($className, $attributeName));
	}

	/**
	 * Add a "set next" rule for the specified parameters.
	 * @param string $pattern Element matching pattern
	 * @param string $methodName Method name to call on the parent element
	 * @param string $paramType (optional) expected class type
	 * @return void
	 */
	function addSetNext($pattern, $methodName, $paramType = null)
	{
		$this->addRule($pattern, new SetNextRule($methodName, $paramType));	
	}

	/**
	 * Add a "set properties" rule for the specified parameters.
	 * @param string $pattern Element matching pattern
	 * @param array $attributeNames Optional names of attributes with custom mappings
	 * @param array $propertyNames Optional property names of attributes to map to
	 * @return void
	 */
	function addSetProperties($pattern, $attributeNames = array(), $propertyNames = array())
	{
		$this->addRule($pattern, new SetPropertiesRule($attributeNames, $propertyNames));
	}

	/**
	 * Add a "set property" rule for the specified parameters.
	 * @param string $pattern Element matching pattern
	 * @param string $name Attribute name containing the property name to be set
	 * @param string $value Attribute name containing the property value to set
	 * @return void
	 */
	function addSetProperty($pattern, $name, $value)
	{
		$this->addRule($pattern, new SetPropertyRule($name, $value));
	}

	/**
	 * Add a "bean property setter" rule for the specified parameters.
	 * @param string $pattern Element matching pattern
	 * @param string $propertyName (optional) Name of property to set
	 * @return void
	 */
	function addBeanPropertySetter($pattern, $propertyName = null)
	{
		$this->addRule($pattern, new BeanPropertySetterRule($propertyName));
	}

	/**
	 * Clear the current contents of the object stack.
	 * @return void
	 */
	function clear()
	{
		$this->match = '';
		$this->bodyTexts = array();
		$this->stack = array();
	}

	/**
	 * Return the top object on the stack without removing it.  If there are no
	 * objects on the stack, return <kbd>null</kbd>.
	 * @return object
	 */
	function &peek($n = null)
	{
		$count = $this->getCount();
		if ($count == 0)
		{
			return ref(null);
		}

		$topIndex = $count - 1;
		if (intval($n) == 0)
		{
			return $this->stack[$topIndex];
		}
		else
		{
			$index = $topIndex - $n;
			if (array_key_exists($index, $this->stack))
			{
				return $this->stack[$index];
			}
			else
			{
				return ref(null);
			}
		}
	}

	/**
	 * Pop the top object off of the stack, and return it.  If there are no
	 * objects on the stack, return <kbd>null</kbd>.
	 * @return object
	 */
	function &pop()
	{
		if ($this->getCount() == 0)
		{
			$nil = ref(null);
			return $nil;
		}

		$element =& $this->stack[$this->getCount() - 1];
		array_pop($this->stack);
		return $element;
	}

	/**
	 * Push a new object onto the top of the object stack.
	 * @param object $object The new object
	 * @return void
	 */
	function push(&$object)
	{
		if ($this->getCount() == 0)
		{
			$this->root =& $object;
		}

		// we have to use & going into the php native functions
		//array_push($this->stack, &$object);
		// NOTE: use this syntax to avoid call_time_pass_by_reference problems
		$this->stack[] =& $object;
	}

	/**
	 * This method allows you to access the root object that has been created
	 * after parsing.
	 * @return object The root object that has been created after parsing or
	 *  null if the digester has not yet parsed the XML document
	 */
	function getRoot()
	{
		return $this->root;
	}

	/**
	 * Provide a hook for lazy configuration of this Digester instance.
	 * @return void
	 */
	function configure()
	{
		if ($this->configured)
		{
			return;
		}

		$this->configured = true;
	}

	/**
	 * Return the top object on the parameters stack without removing it.  If
	 * there are no objects on the stack, return <kbd>null</kbd>.
	 *
	 * The parameters stack is used to store {@link CallMethodRule}
	 * parameters.
	 * @param int $n Optional position on stack
	 * @return object
	 */
	function &peekParams($n = null)
	{
		$count = count($this->params);
		if ($count == 0)
		{
			return ref(null);
		}

		$topIndex = $count - 1;
		if (intval($n) == 0)
		{
			return $this->params[$topIndex];
		}
		else
		{
			$index = $topIndex - $n;
			if (array_key_exists($index, $this->params))
			{
				return $this->params[$index];
			}
			else
			{
				return ref(null);
			}
		}
	}

	/**
	 * Pop the top object off of the parameters stack, and return it.  If there
	 * are no objects on the stack, return <kbd>null</kbd>.
	 *
	 * The parameters stack is used to store {@link CallMethodRule}
	 * parameters.
	 * @return object
	 */
	function &popParams()
	{
		if (count($this->params) == 0)
		{
			$nil =& ref(null);
			return $nil;
		}

		$element =& $this->params[count($this->params) - 1];
		array_pop($this->params);
		return $element;
	}

	/**
	 * Push a new object onto the top of the parameters stack.
	 *
	 * The parameters stack is used to store {@link CallMethodRule} parameters.
	 * @param object $object the new object
	 * @return void
	 */
	function pushParams(&$object)
	{
		//array_push($this->params, &$object);
		// NOTE: use this syntax to avoid call_time_pass_by_reference problems
		$this->params[] =& $object;
	}

	/**
	 * Register a DTD to validate against when parsing this XML document.
	 *
	 * NOTE: This method is part of a quick hack to get some feedback in validating since
	 * PHP's functions for validating XML documents are super weak.
	 *
	 * @return void
	 */
	function register($publicID, $entityURL)
	{
		$this->dtdURL = $entityURL;
	}

	/**
	 * NOTE: This method is part of a quick hack to get some feedback in
	 * validating since PHP's functions for validating XML documents are super
	 * weak. It uses either the domxml validation or a commandline xmllint
	 * call.
	 *
	 * @return boolean Whether or not the document validates, or true if
	 * validation is disabled
	 */
	function checkValid(&$inputFile)
	{
		if ($this->validating)
		{
			// try to do it from the commandline (unix only)
			if (substr(strtoupper(PHP_OS), 0, 3) != 'WIN' &&
				($xmllint = @exec('which xmllint 2>/dev/null')))
			{
				return $this->_checkValidXmlLint($inputFile, $xmllint);	
			}
			//else if (function_exists('domxml_open_file'))
			//{
			//	return $this->_checkValidDomXml($inputFile);	
			//}

			// if we have no validation engines, just return true
			return true;
		}
	}

	/**
	 * Call out to the xmllint commandline
	 */
	function _checkValidXmlLint(&$inputFile, $xmllint)
	{
		$retval = null;
		$errors = array();

		//@exec($xmllint . ' --noout --valid ' . $inputFile->getPath() . ' 2>&1', $errors, $retval);
		@exec($xmllint . ' --noout --nonet --dtdvalid ' . $this->dtdURL . ' ' . $inputFile->getPath() . ' 2>&1', $errors, $retval);

		// if all passed a retval of 0 will be supplied, so anything > 0 is bad
		if ($retval)
		{
			// drop last error, which reports that we are not valid
			array_pop($errors);
			throw_exception(new RootException('DTD validation failed!' . "\n\n" . implode("\n", $errors) . "\n"));
			return false;
		}

		return true;
	}

	/**
	 * NOTE: this method will not report line numbers
	 */
	function _checkValidDomXml(&$inputFile)
	{
		$xmldoc = @domxml_open_file($inputFile->getPath());
		$result = array();
		@$xmldoc->validate($result);
		$valid = (count($result) == 0) ? true : false;
		if (!$valid)
		{
			foreach ($result as $error)
			{
				$errors[] = $inputFile->getPath() . ': ' . trim($error['errormessage']);
			}

			throw_exception(new RootException('DTD validation failed!' . "\n\n" . implode("\n", $errors) . "\n"));
			return false;
			
		}

		return true;
	}
}
?>
