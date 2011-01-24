<?php
/**
 * @package horizon.io
 * @author Dan Allen
 */
class FileLock
{
	/**
	 * Holds the wrapper object for a native resource stream.
	 * @var resource
	 */
	var $fd;

	var $shared;

	/**
	 * If an operation is requested that is not permitted by the current lock condition,
	 * allow PHP to automatically hold the requesting process for a set amount of time
	 * freeing the application from having to manage this detail.
	 * @var boolean
	 */
	var $blocking;

	var $valid;

	function FileLock(&$fd, $shared = true, $blocking = true)
	{
		$this->fd =& $fd;
		$this->shared = $shared;
		$this->blocking = $blocking;
		$mode = 0;
		$mode |= ($shared ? LOCK_SH : LOCK_EX);
		// prevent blocking if shared or explicitly requested
		if ($shared || !$blocking)
		{
			$mode |= LOCK_NB;
		}

		// @todo throw an exception if an error occurs rather than silencing
		$this->valid = @flock($this->fd, $mode);
	}

	function isBlocking()
	{
		return $this->blocking;
	}

	function isShared()
	{
		return $this->shared;
	}

	// TODO: figure out a way to find out if the file pointer is still open
	// attempt a read?
	function isValid()
	{
		return $this->valid;
	}

	function release()
	{
		@flock($this->fd, LOCK_UN);
		$this->valid = false;
	}
}
?>
