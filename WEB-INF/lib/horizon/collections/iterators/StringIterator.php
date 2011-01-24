<?php
/* $Id: StringIterator.php 212 2005-06-21 21:23:55Z mojavelinux $
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
 * Class <b>StringIterator</b> provides an iterator for strings.
 *
 * This class offers an implementation of the {@link IIterator} interface
 * for iterating over the characters in a string. As with any iterator,
 * changing the structure of the object iterated over (e.g. changing the string
 * to a different string) might result in unexpected behavior. However it is
 * possible to change the character returned by {@link getCurrent()}. For
 * example:
 *
 * <code>
 *   $string = 'Encrypt me!';
 *   $key    = array(-1, 3, -2, 0, 0, 1);
 *   $size   = count($key);
 *   $index  = 0;
 *   for ($it =& new StringIterator($string); $it->hasNext(); $it->next()) 
 *   {
 *       $char  =& $it->getCurrent();
 *       $char  =  chr(ord($char) + $key[$index]);
 *       $index =  ($index + 1) % $size;
 *   }
 *   print 'Encrypted string: ' . $string;
 * </code>
 *
 * The above encryption algorithm is actually unbreakable if the key-array
 * is at least as long as the encrypted string. (It's a one-time-pad in that
 * case.) The decryption algorithm is left as an exercise for the reader...
 *
 * @package horizon.collections.iterators
 * @author Vincent Oostindie
 * @author Dan Allen
 */
class StringIterator extends IIterator
{
    // DATA MEMBERS

    /***
     * The string to iterate over
     * @var string
     ***/
    var $string;

    /***
     * The current index in the string
     * @var int
     ***/
    var $index;

    /***
     * The current character
     * @var char
     ***/
    var $char;

    /***
     * The total length of the string
     * @var int
     ***/
    var $size;

    // CREATORS

    /***
     * Construct a new {@link StringIterator}
     * @param $string the string to iterate over
     ***/
    function StringIterator(&$string) 
    {
        $this->string =& $string;
        $this->size   =  strlen($string);
        $this->reset();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function reset() 
    {
        $this->index = 0;
        $this->char  = ($this->size) ? $this->string{0} : '';
    }

    /***
     * @returns void
     ***/
    function next() 
    {
        $this->string{$this->index} = $this->char{0};
        $this->index++;
        $this->char = ($this->index < $this->size) 
            ? $this->string{$this->index} : '';
    }

    // ACCESSORS

    /***
     * @returns bool
     ***/
    function hasNext() 
    {
        return ($this->index < $this->size);
    }

    /***
     * Return a reference to the current character
     * @returns char
     ***/
    function &getCurrent() 
    {
        return $this->char;
    }
}
?>
