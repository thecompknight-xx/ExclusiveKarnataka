<?php
/* $Id: NumberIterator.php 212 2005-06-21 21:23:55Z mojavelinux $
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

import('horizon.collections.IIterator');

/**
 * Class <b>NumberIterator</b> is an iterator for generating consecutive
 * numbers.
 *
 * On creation the total number of numbers to generate, the starting number
 * and the stepping number must be set. The latter two are optional.
 *
 * Consider a query that returns many columns, and there's a need to divide
 * the result over multiple pages (with a {@link PagedQuery}). A link to
 * each page in the result is useful, and some simple navigation to advance to
 * the next page or jump back to the previous one might also come in handy.
 *
 * It is possible to set the number to start with, as well as the
 * stepping. A simple application is the generation of
 * the multiplication table of some number <var>$n</var>:
 *
 * <code>$it =& new NumberIterator(10, $n, $n);</code>
 *
 * @package horizon.collections.iterators
 * @author Vincent Oostindie
 * @author Dan Allen
 */
class NumberIterator extends IIterator
{
    // DATA MEMBERS

    /***
     * The total number of numbers to generate
     * @var int
     ***/
    var $size;

    /***
     * The first number that should be generated
     * @var int
     ***/
    var $base;

    /***
     * The stepping size
     * @var int
     ***/
    var $step;

    /***
     * The index of the current number
     * @var int
     ***/
    var $index;

    /***
     * The current number
     * @var int
     ***/
    var $current;

    // CREATORS

    /**
     * Construct a new {@link NumberIterator}
	 *
     * @param $size the total number of numbers to generate
     * @param $base the first number
     * @param $step the number to add with each consecutive step
     */
    function NumberIterator($size, $base = 1, $step = 1) 
    {
        $this->size = $size;
        $this->base = $base;
        $this->step = $step;
        $this->reset();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function reset() 
    {
        $this->index = 0;
        $this->current = $this->base;
    }

    /***
     * @returns void
     ***/
    function next() 
    {
        $this->index++;
        $this->current += $this->step;
    }

    // ACCESSORS

    /***
     * @returns bool
     ***/
    function hasNext() 
    {
        return $this->index < $this->size;
    }

    /***
     * @returns int
     ***/
    function &getCurrent() 
    {
        return $this->current;
    }
}
?>
