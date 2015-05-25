<?php
class Cron_Core {

	protected $Db;


	public function __construct($Db) {
		$this->Db = &$Db;
	}
	
	public function run() {
		$this->temporaryFiles();
	}

	public function temporaryFiles() {
		$rows = $this->Db->getRows("
				SELECT id FROM `file` 
				WHERE 
					status = 0 && 
					created < :time",
				[":time" => REQUEST_TIME - 60*60*24]);
		foreach ($rows as $row) {
			$File = newClass("File", $this->Db, $row->id);
			$File->delete();
			addlog($this->Db, "file", t("Deleted temporary file :name", "en", [":name" => $File->get("name")]), null, "success");
		}
	}

}