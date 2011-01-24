<?php
/* $Id: Assert.php 306 2005-07-21 04:14:42Z mojavelinux $
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

import('horizon.util.unittest.AssertionFailedException');

/**
 * <p>Special thanks goes to Kent Beck and Erich Gamma for writing JUnit,
 * the inspiration behind this unit test implementation.</p>
 *
 * @package horizon.util.unittest
 * @author Dan Allen
 *
 * TODO: implement assertSame family
 */
class Assert extends Object
{
	function assertTrue($condition, $message = null)
	{
		if (!$condition)
		{
			Assert::fail($message);
		}
	}

	function assertFalse($condition, $message = null)
	{
		return Assert::assertTrue(!$condition, $message);
	}

	/**
	 * Fails a test with a given message.
	 *
	 * @throws AssertionFailedException
	 */
	function fail($message = null)
	{
		throw_exception(new AssertionFailedException($message));
	}
	
	function assertEquals($expected, $actual, $message = null)
	{
		// TODO: handle case of object comparison here
		if ($expected !== $actual)
		{
			Assert::failNotEquals($expected, $actual, $message);
		}
	}

	function failNotEquals($expected, $actual, $message)
	{
		if (!is_null($message))
		{
			$message .= ' ';	
		}

		Assert::fail($message . 'expected -' . $expected . '- but result was -' . $actual . '-');
	}

	function assertNull($object, $message = null)
	{
		return Assert::assertTrue(is_null($object), $message);
	}

	function assertNotNull($object, $message = null)
	{
		return Assert::assertTrue(!is_null($object), $message);
	}
}
?>
