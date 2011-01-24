<?php
/* $Id: TestSuite.php 306 2005-07-21 04:14:42Z mojavelinux $
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

import('horizon.util.unittest.TestResult');

/**
 * A <strong>TestSuite</strong> is a composite of tests.  It runs a collection
 * of test cases. Here is an example using the dynamic test definition.
 *
 * <p>The constructor either takes a single test case (one method) or the
 * entire class, from which the tests to be run will be extracted
 * automatically.</p>
 *
 * <p>NOTE: When creating a test suite, there is actually one instance of the
 * test class per method being executed.  Thus, instance variables in the test
 * class with not be accessible from one test method to the next.</p>
 *
 * <p>NOTE: Object references are excluded during the creation of the tests
 * intentionally since it is not necessary to ensure a single instance.  On
 * the contrary, each method should belong to its own instance of the test.</p>
 *
 * <p>Special thanks goes to Kent Beck and Erich Gamma for writing JUnit,
 * the inspiration behind this unit test implementation.</p>
 */
class TestSuite extends Object // implements Test
{
	var $tests = array();

	var $name;

	function TestSuite($clazz = null)
	{
		if (is_null($clazz))
		{
			$this->setName('Test Suite');
		}
		else
		{
			$this->setName($clazz->getName());
			
			$superClazz = $clazz;
			while (!is_null($superClazz))
			{
				$methods = $superClazz->getDeclaredMethods();
				$names = array();
				for ($i = 0; $i < count($methods); $i++)
				{
					$this->addTestMethod($methods[$i], $names, $clazz);
				}

				$superClazz = $superClazz->getSuperclass();
			}
		}
	}

	function run(&$result)
	{
		$this->runWith($result);
	}

	function runWith(&$result)
	{
		for ($i = 0; $i < count($this->tests); $i++)
		{
			//if ($result->shouldStop())
			//{
			//	break;
			//}

			$test = $this->tests[$i];
			$this->runTest($test, $result);
		}
	}

	/**
	 * Return the test at the specified index.
	 */
	function testAt($index)
	{
		if (!isset($this->tests[$index]))
		{
			return null;
		}

		return $this->tests[$index];
	}

	/**
	 * Run the specified test, placing the result in the specified TestResult
	 * instance.
	 */
	function runTest(&$test, &$result)
	{
		$test->runWith($result);
	}

	function addTestMethod($method, &$names, $clazz)
	{
		// make sure we haven't already hit this method
		// and that it is indeed a test method
		if (in_array($method, $names) || !$this->isTestMethod($method))
		{
			return;	
		}

		$names[] = $method;
		$this->addTest($this->createTest($clazz, $method));
	}

	function createTest($clazz, $method)
	{
		$test = $clazz->newInstance();
		$test->setName($method);
		return $test;
	}

	function getTestCount()
	{
		return count($this->tests);
	}

	function getTests()
	{
		return $this->tests;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function getName()
	{
		return $this->name;
	}

	/**
	 * Add a single test to the suite
	 *
	 * @return void
	 */
	function addTest($test)
	{
		$this->tests[] = $test;
	}

	/**
	 * Add all the test from the provided class to this suite.
	 *
	 * @return void
	 */
	function addTestSuite($testClazz)
	{
		$this->addTest(new TestSuite($testClazz));
	}

	/**
	 * Determine if this method is a test method (aka, begins with "test")
	 *
	 * @return boolean Whether or not this method is a test method
	 */
	function isTestMethod($method)
	{
		return (strpos($method, 'test') === 0);
	}

	/**
	 * Count the number of individual test cases that will be run
	 * in this suite.
	 *
	 * @return int The number of test cases that will be run
	 */
	function countTestCases()
	{
		$count = 0;
		for ($i = 0; $i < count($this->tests); $i++)
		{
			$test = $this->tests[$i];
			$count += $test->countTestCases();
		}

		return $count;
	}
}
?>
