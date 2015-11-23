<?php
/**
 * Contains update class
 */

/**
 * Update class
 *
 * Serves as a base for updates
 * 
 * @author Eric Höglander
 */
class Update_Core {

	/**
	 * Database object
	 * @var \Db_Core
	 */
	protected $Db;
	

	/**
	 * Constructor
	 * @param \Db_Core $Db
	 */
	public function __construct($Db) {
		$this->Db = $Db;
	}
	
	/**
	 * Run the update
	 * @return bool
	 */
	public function execute() {
		return true;
	}

}