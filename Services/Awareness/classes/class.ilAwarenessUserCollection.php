<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilAwarenessUserCollection
{
	protected $users;

	/**
	 * Get instance
	 *
	 * @return ilAwarenessUserCollection user collection
	 */
	static function getInstance()
	{
		return new ilAwarenessUserCollection();
	}

	/**
	 * Add user
	 *
	 * @param integer $a_id user id
	 */
	function addUser($a_id)
	{
		$this->users[$a_id] = $a_id;
	}

	/**
	 * Get users
	 *
	 * @return array array of user ids (integer)
	 */
	function getUsers()
	{
		return $this->users;
	}


}

?>