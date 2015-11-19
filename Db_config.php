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
			if ($_SERVER['HTTP_HOST'] == 'blog.beyondlocal.dev'){
				$DB_SERVER = "127.0.0.1";
				$DB_USER = "root";
				$DB_PASSWORD = "123";
				$DB = "beyond_local";
				$this->dbConnect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB);
			}elseif($_SERVER['HTTP_HOST'] == 'blogdemo.beyondlocal.co.uk:3080' || $_SERVER['HTTP_HOST'] == 'blogdemo.beyondlocal.co.uk'){
				$DB_SERVER = "127.0.0.1";
				$DB_USER = "demobey1_BLV2";
				$DB_PASSWORD = "PromoTippedWaiveCynic85";
				$DB = "demobey1_BLV2";
				$this->dbConnect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB);
			} else {
				$DB_SERVER = "127.0.0.1";
				$DB_USER = "blocal_BLuser";
				$DB_PASSWORD = "FreaksEvokeDabShirr84";
				$DB = "blocal_beyond_local";
				$this->dbConnect($DB_SERVER, $DB_USER, $DB_PASSWORD, $DB);
			}
		}

	}
?>