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

//define the azure_speech_service class
if (!class_exists('azure_speech_service')) {
	class azure_speech_service {

		private static function getTokenUrl() {
			return "https://".self::getRegion().".api.cognitive.microsoft.com/sts/v1.0/issueToken";
		}

		private static function getSynthesisApiUrl() {
			return "https://".self::getRegion().".tts.speech.microsoft.com/cognitiveservices/v1";
		}

		private static function getVoicesApiUrl() {
			return "https://".self::getRegion().".tts.speech.microsoft.com/cognitiveservices/voices/list";
		}

		private static function getRegion() {
			$region = $_SESSION['tts_recordings']['azure_region']['text'];
			if (empty($region)) {
				$region = "eastus";
			}
			return $region;
		}

		private static function getSubscriptionKey() {
			return $_SESSION['tts_recordings']['azure_subscription_key']['text'];
		}

		public static function getToken() {
			//use key 'http' even if you send the request to https://...
				$options = array(
					'http' => array(
						'header'  => "Ocp-Apim-Subscription-Key: ".self::getSubscriptionKey()."\r\n" .
						"content-length: 0\r\n",
						'method'  => 'POST',
					),
				);
				$context  = stream_context_create($options);

			//get the Access Token
				$access_token = file_get_contents(self::getTokenUrl(), false, $context);

			return $access_token;
		}

		public static function getVoicesList($access_token = "") {
			//get access token if not passed
				if (!isset($access_token) || empty($access_token)) {
					$access_token = self::getToken();
				}

			//use key 'http' even if you send the request to https://...
				$options = array(
					'http' => array(
						'header'  => "Authorization: "."Bearer ".$access_token."\r\n" .
									"content-length: 0\r\n",
						'method'  => 'GET',
					),
				);
				$context  = stream_context_create($options);

			//get the Voices list
				$voices_list = file_get_contents(self::getVoicesApiUrl(), false, $context);
				if (!$voices_list) {
					return array();
				}

			return json_decode($voices_list, true);
		}

		public static function synthesizeSsml($ssml, $access_token = "") {
			//get access token if not passed
				if (!isset($access_token) || empty($access_token)) {
					$access_token = self::getToken();
				}

			$options = array(
				'http' => array(
					'header'  => "Content-type: application/ssml+xml\r\n" .
								"X-Microsoft-OutputFormat: riff-16khz-16bit-mono-pcm\r\n" .
								"Authorization: "."Bearer ".$access_token."\r\n" .
								"X-Search-AppId: 07D3234E49CE426DAA29772419F436CA\r\n" .
								"X-Search-ClientID: 1ECFAE91408841A480F00935DC390960\r\n" .
								"User-Agent: TTSPHP\r\n" .
								"content-length: ".strlen($ssml)."\r\n",
					'method'  => 'POST',
					'content' => $ssml,
				),
			);
			
			$context  = stream_context_create($options);

			// get the wave data
				$result = file_get_contents(self::getSynthesisApiUrl(), false, $context);

			return $result;
		}

	}
}

?>