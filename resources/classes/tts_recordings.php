<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2020
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Amer Tahir <amertahir@gmail.com>
*/

//define the tts_recordings class
if (!class_exists('tts_recordings')) {
	class tts_recordings {

		/**
		 * declare public variables
		 */
		public $domain_uuid;

		/**
		 * declare private variables
		 */
		private $app_name;
		private $app_uuid;
		private $permission_prefix;
		private $list_page;
		private $table;
		private $uuid_prefix;
		private $toggle_field;
		private $toggle_values;

		/**
		 * called when the object is created
		 */
		public function __construct() {
			$this->domain_uuid = $_SESSION['domain_uuid'];

			//assign private variables
				$this->app_name = 'recordings';
				$this->app_uuid = '7f872095-2c0c-4115-acdd-9992b9edf7de';
				$this->permission_prefix = 'tts_recording_';
				$this->list_page = 'recordings.php';
				$this->table = 'tts_recordings';
				$this->uuid_prefix = 'tts_recording_';

		}

		/**
		 * called when there are no references to a particular object
		 * unset the variables used in the class
		 */
		public function __destruct() {
			foreach ($this as $key => $value) {
				unset($this->$key);
			}
		}

		/**
		 * list recordings
		 */
		public function list_recordings() {
			$sql = "select tts_recording_uuid, recording_filename ";
			$sql .= "from v_tts_recordings ";
			$sql .= "where domain_uuid = :domain_uuid ";
			$parameters['domain_uuid'] = $this->domain_uuid;
			$database = new database;
			$result = $database->select($sql, $parameters, 'all');
			if (is_array($result) && @sizeof($result) != 0) {
				foreach ($result as &$row) {
					$recordings[$_SESSION['switch']['recordings']['dir'].'/'.$_SESSION['domain_name']."/".$row['recording_filename']] = $row['recording_filename'];
				}
			}
			unset($sql, $parameters, $result, $row);
			return $recordings;
		}
	
		/**
		 * delete records
		 */
		public function delete($records) {
			if (permission_exists($this->permission_prefix.'delete') && permission_exists('recording_delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//get recording filename, build delete array
							foreach ($records as $x => $record) {
								if ($record['checked'] == 'true' && is_uuid($record['uuid'])) {

									//get filename
										$sql = "select recording_filename from v_tts_recordings ";
										$sql .= "where domain_uuid = :domain_uuid ";
										$sql .= "and tts_recording_uuid = :recording_uuid ";
										$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
										$parameters['recording_uuid'] = $record['uuid'];
										$database = new database;
										$filenames[] = $database->select($sql, $parameters, 'column');
										unset($sql, $parameters);

									//build delete array
										$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
										$array[$this->table][$x]['domain_uuid'] = $_SESSION['domain_uuid'];
									
									//delete from recordings table as well
										$array['recordings'][$x]['recording_uuid'] = $record['uuid'];
										$array['recordings'][$x]['domain_uuid'] = $_SESSION['domain_uuid'];
								}
							}

						//delete the checked rows
							if (is_array($array) && @sizeof($array) != 0) {

								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);

								//delete recording files
									if (is_array($filenames) && @sizeof($filenames) != 0) {
										foreach ($filenames as $filename) {
											if (file_exists($_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".$filename)) {
												@unlink($_SESSION['switch']['recordings']['dir']."/".$_SESSION['domain_name']."/".$filename);
											}
										}
									}

								//clear the destinations session array
									if (isset($_SESSION['destinations']['array'])) {
										unset($_SESSION['destinations']['array']);
									}

								//set message
									message::add($text['message-delete']);
							}
							unset($records);
					}
			}
		} //method

	} //class
}

?>