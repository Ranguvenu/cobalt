<?php
namespace local_employees\team;
class actions{
	//stores the db variable
	public $db;
	//stores the user
	public $user;
	public function __construct($db, $user){
		global $DB, $USER;
		$this->db = $db ? $db : $DB;
		$this->user = $user ? $user : $USER;
	}
	public function team_approvals_view(){

	}
}
