<?php
/* $Id: RulesBase.php 212 2005-06-21 21:23:55Z mojavelinux $
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

import('horizon.xml.digester.Rules');
import('horizon.collections.iterators.ListIterator');

/**
 * Default implementation of the {@link Rules} interface that supports
 * the standard rule matching behavior.
 *
 * This class can also be used as a base class for specialized
 * {@link Rules} implementations.
 *
 * The matching policies implemented by this class support two different
 * types of pattern matching rules:
 *
 * <ul>
 * <li><b>Exact Match</b> - A pattern "a/b/c" exactly matches a
 *     <code>&lt;c&gt;</code> element, nested inside a <code>&lt;b&gt;</code>
 *     element, which is nested inside an <code>&lt;a&gt;</code> element.</li>
 * <li><b>Tail Match</b> - A pattern "&#42;/a/b" matches a
 *     <code>&lt;b&gt;</code> element, nested inside an <code>&lt;a&gt;</code>
 *      element, no matter how deeply the pair is nested.</li>
 * </ul>
 *
 * @author Dan Allen <dan.allen@mojavelinux.com> <br />
 *  <b>Credits:</b> Craig R. McClanahan
 * @package horizon.xml.digester
 */
class RulesBase extends Rules
{
	var $cache = array();

	var $digester = null;

	var $namespaceURI = null;

	var $rules = array();

	function getDigester()
	{
		return $this->digester;
	}

	function setDigester(&$digester)
	{
		$this->digester =& $digester;
	}

	function getNamespaceURI()
	{
		return $this->namespaceURI;
	}

	function setNamespaceURI($namespaceURI)
	{
		$this->namespaceURI = $namespaceURI;
	}

	function add($pattern, &$rule)
	{
		$list =& $this->cache[$pattern];
		if (is_null($list))
		{
			$list = array();
		}

		$list[] =& $rule;
		$this->rules[] =& $rule;

		if (!is_null($this->digester))
		{
			$rule->setDigester($this->digester);
		}

		if (!is_null($this->namespaceURI))
		{
			$rule->setNamespaceURI($this->namespaceURI);
		}
	}

	function clear()
	{
		$this->cache = array();
		$this->rules = array();
	}

	/**
	 * Return a List of all registered Rule instances that match the specified
	 * nesting pattern, or a zero-length List if there are no matches.  If more
	 * than one Rule instance matches, they <b>must</b> be returned
	 * in the order originally registered through the {@link add()}
	 * method.
	 * @param string $namespaceURI
	 * @param string $pattern
	 * @return array
	 */
	function match($namespaceURI, $pattern)
	{
		$rulesList = $this->lookup($namespaceURI, $pattern);	
		if (is_null($rulesList) || count($rulesList) < 1)
		{
			$longKey = '';
			$keys = array_keys($this->cache);
			for ($i = 0; $i < count($keys); $i++)
			{
				$key = $keys[$i];
				if (strpos($key, '*/') === 0)
				{
					if ($pattern == substr($key, 2) || substr($pattern, -strlen($key) + 1) == substr($key, 1))
					{
						if (strlen($key) > strlen($longKey))
						{
							$rulesList = $this->lookup($namespaceURI, $key);
							$longKey = $key;
						}
					}
				}
			}
		}

		if (is_null($rulesList))
		{
			$rulesList = array();
		}

		return $rulesList;
	}

	function &rules()
	{
		return $this->rules;
	}

	/**
	 * Return an array of Rule instances for the specified pattern that also
	 * match the specified namespace URI (if any).  If there are no such rules,
	 * return <kbd>null</kbd>.
	 * @param string $namespaceURI
	 * @param string $pattern
	 * @return array
	 */
	function lookup($namespaceURI, $pattern)
	{
		if (!isset($this->cache[$pattern]))
		{
			return null;
		}

		$list = $this->cache[$pattern];
		if (strlen($namespaceURI) == 0)
		{
			return $list;
		}
		
		// Select only Rules that match on the specified namespace URI
		$results = array();
		for ($it =& new ListIterator($list); $it->hasNext();)
		{
			$item =& $it->next();

			if (is_null($item->getNamespaceURI()) || $this->namespaceURI == $item->getNamespaceURI())
			{
				$results[] =& $item;	
			}
		}

		return $results;
	}
}
?>
