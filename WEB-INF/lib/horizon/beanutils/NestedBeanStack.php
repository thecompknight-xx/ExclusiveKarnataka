<?php
/**
 * @package horizon.beanutils
 * @author Dan Allen
 */
class NestedBeanStack
{
	var $index = -1;

	var $stack = array();

	function push(&$o)
	{
		$this->index++;
		$this->stack[$this->index] =& $o;
	}

	function &peek($depth = 0)
	{
		if ($this->index >= 0 && $depth <= $this->index)
		{
			return $this->stack[$this->index - $depth];
		}

		return ref(null);
	}

	function &pop()
	{
		if ($this->index >= 0)
		{
			$current =& $this->getCurrent();	
			array_pop($this->stack);

			return $current;
		}

		return ref(null);
	}

	function &getCurrent()
	{
		return $this->peek(0);
	}

	function &compact()
	{
		$result =& $this->getCurrent();
		// release the stack
		unset($this->stack);
		$this->stack = array();
		return $result;
	}
}
?>
