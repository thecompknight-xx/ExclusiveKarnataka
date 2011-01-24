<?php
/* $Id: TestCase.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('horizon.beanutils.MethodUtils');
import('horizon.util.unittest.Assert');
import('horizon.util.unittest.TestResult');

/**
 * A <strong>TestCase</strong> is the primary element for creating testing
 * methods.  It should be extended by an implementing class that defines
 * a handful of methods for testing units of code.  These methods are
 * typically prefixed with 'test'.  Two special methods, setup() and 
 * tearDown(), are executed before and after each test method, respectively.
 *
 * <p>Each test runs in its own instance so there can be no side effects among
 * test runs.</p>
 *
 * <p>Special thanks goes to Kent Beck and Erich Gamma for writing JUnit,
 * the inspiration behind this unit test implementation.</p>
 *
 * @package horizon.util.unittest
 * @author Dan Allen
 */
class TestCase extends Assert // implements Test
{
	/**
	 * The name of the test case
	 */
	var $name;

	function TestCase($name = null)
	{
		$this->setName($name);
	}

	function countTestCases()
	{
		return 1;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function getName()
	{
		return $this->name;
	}

	function &createResult()
	{
		$result =& new TestResult();
		return $result;
	}

	/**
	 * Runs the test case and collects the results in a newly created
	 * TestResult.
	 *
	 * @return TestResult
	 */
	function &run()
	{
		$result =& $this->createResult();
		return $this->runWith($result);	
	}

	/**
	 * Runs the test case and collects the results in the existing
	 * TestResult parameter.
	 */
	function &runWith(&$result)
	{
		$result->run($this);
		return $result;
	}

	/**
	 * Runs the bare test sequence
	 *
	 * @throws RootException
	 */
	function runBare()
	{
		$this->setUp();
		$this->runTest();
		$this->tearDown();
	}

	/**
	 * @throws RootException If an exception is thrown calling method
	 */
	function runTest()
	{
		$this->assertNotNull($this->name);
		// try {
		MethodUtils::invokeMethod($this, $this->name);	
		// } catch (NoSuchMethodException $e) {
		if ($e = catch_exception('NoSuchMethodException'))
		{
			Assert::fail('Method ' . $this->name . ' not found');
		}
		// }
	}

	/**
	 * Sets up the environment.
	 *
	 * @abstract
	 */
	function setUp() {}

	/**
	 * Tears down the environment.
	 *
	 * @abstract
	 */
	function tearDown() {}
}
?>
