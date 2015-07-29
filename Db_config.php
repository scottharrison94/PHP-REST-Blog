<?php
	class DB_CONFIG extends REST {

		protected $siteName = '';

		/*
		 *  Call the correct connection settings based on the site name
		*/
		protected function setDBconfig($siteName) {
			switch ($siteName) {
				case 'beyond_local':
					$this->beyondLocalConfig();
					break;
				
				default:
					# code...
					break;
			}
		}

		/*
		 *  Connect to Database
		*/
		private function dbConnect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB){
			$this->pdo = new PDO('mysql:host='.$DB_SERVER.';dbname='.$DB, $DB_USER, $DB_PASSWORD);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
		}


		/*
		 *  Database conection settings for all sites below
		*/
		private function beyondLocalConfig() {
			$DB_SERVER = "127.0.0.1";
			$DB_USER = "root";
			$DB_PASSWORD = "123";
			$DB = "beyond_local";
			$this->dbConnect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB);
		}

	}
?>