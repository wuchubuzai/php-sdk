<?php
/**
 * Wuchubuzai PHP-SDK
 * @version 2012.04.02
 * @license See the included LICENSE file for more information [ https://github.com/wuchubuzai/php-sdk/LICENSE ]
 * @link http://www.wuchubuzai.com/
 * Twitter information:  https://twitter.com/wuchubuzai_labs
 *                       https://twitter.com/wuchubuzai_dev
 */
class WuchubuzaiAPI {

	public $apiUrl = 'dev.api.wuchubuzai.com';
	public $useHttps = false;
	private $apiKey;
	private $apiSecret;
	public $output;
	public $debug = false;

	public function __construct() {

		if (file_exists(dirname(__FILE__) . '/config.inc.php')) require_once dirname(__FILE__) . '/config.inc.php';

		if (API_URL) $this->apiUrl = API_URL;

		if (USE_HTTPS) $this->apiUrl = 'https://' . $this->apiUrl;
		else $this->apiUrl = 'http://' . $this->apiUrl;

		if (API_KEY) $this->apiKey = API_KEY;
		else throw new WuchubuzaiAPIException("API_KEY must be defined in config.inc.php");

		if (API_SECRET_KEY) $this->apiSecret = API_SECRET_KEY;
		else throw new WuchubuzaiAPIException("API_SECRET_KEY must be defined in config.inc.php");

	}

	public function getApiUrl() {
		return $this->apiUrl;
	}

	private function sendPackage($method, $objectType, $objectId=null, $attributes=null, $rest_key=null, $targetLanguage=null)  {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = array();
		if ($attributes == null) {
			$data = array('appId' => APPLICATION_ID,'id' => $objectId);
		} else {
			if (strtoupper($method) == 'GET' || strtoupper($method) == 'SEARCH') {
				$data = array('appId' => APPLICATION_ID,'id' => $objectId,'cols' => $attributes);
			} else if (strtoupper($method) == 'PUT') {
				if (!isset($objectId) && !key_exists('id', $attributes)) {
					throw new WuchubuzaiAPIException("id is mandatory for a PUT request");
				} else {
					if (isset($objectId)) $data['id'] = $objectId;
				}
			}
		}

		if (isset($targetLanguage)) $data['locale'] = $targetLanguage;
		if (isset($rest_key)) $data['rest_key'] = $rest_key;

		if ($this->debug) curl_setopt($ch, CURLOPT_VERBOSE, true);

		if (strtoupper($method) == 'GET') {
			curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/' . $objectType . '/?' . http_build_query($data));
		} else {
			if ($attributes == null) {
				throw new WuchubuzaiAPIException("attributes are required for " . strtoupper($method));
			} else {
				foreach ($attributes as $k => $v) {
					if ($v != null) {
						$data[$k] = $v;
					}
				}
			}

			curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/' . $objectType . '/');

			if (strtoupper($method) == 'POST') {
				curl_setopt($ch, CURLOPT_POST, 1);
			} else {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
			}

			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			if ($this->debug) var_dump($data);
		}

		$output = curl_exec($ch);
		$wuchuOutput = new WuchubuzaiAPIOutput();

		if(!$output) {
			throw new WuchubuzaiAPIException("empty output from cURL against: " + $this->getApiUrl());
			return array('api_error' => 'null output received from API', 'raw_data' => $data, 'env' => $env, 'objectType' => $objectType, 'attributes' => $attributes);
		} else {
			$wuchuOutput->raw = $output;
		}

		$out = @json_decode($output, TRUE);
		if(!$out) {
			throw new WuchubuzaiAPIException("failed to decode json");
			return array('api_error' => json_last_error(), 'raw_output' => $output, 'env' => $env, 'objectType' => $objectType, 'rest_key' => $rest_key, 'attributes' => $attributes);
		} else {
			unset($out['wuchubuzai_api']);
			$wuchuOutput->json = $out;
		}

		$this->output = $wuchuOutput;
		return $this->output;

	}

	public function enableDebug() {
		$this->debug = true;
	}

	public function disableDebug() {
		$this->debug = false;
	}

	public function get($objectType, $objectId, $rest_key=null, $attributes=null) {
		if ($objectType == null) throw new WuchubuzaiAPIException("objectType is required for GET");
		if ($objectId == null) throw new WuchubuzaiAPIException("objectId is required for GET");
		if ($attributes != null) if (!is_array($attributes)) throw new WuchubuzaiAPIException("attributes must be an array for GET");
		return $this->sendPackage('GET', $objectType, $objectId, $attributes, $rest_key);
	}

	public function post($objectType, $attributes) {
		if ($objectType == null) throw new WuchubuzaiAPIException("objectType is required for POST");
		if ($attributes != null) if (!is_array($attributes)) throw new WuchubuzaiAPIException("attributes must be an array for POST");
		return $this->sendPackage('POST', $objectType, $objectId=null, $attributes);
	}

	public function put($objectType, $objectId, $rest_key, $attributes) {
		if ($objectType == null) throw new WuchubuzaiAPIException("objectType is required for PUT");
		if ($objectId == null) throw new WuchubuzaiAPIException("objectId is required for PUT");
		if ($rest_key == null) throw new WuchubuzaiAPIException("rest_key is required for PUT");
		if ($attributes != null) if (!is_array($attributes)) throw new WuchubuzaiAPIException("attributes must be an array for PUT");
		return $this->sendPackage('PUT', $objectType, $objectId, $attributes, $rest_key);
	}

	public function search($objectType, $rest_key, $attributes) {
		if ($objectType == null) throw new WuchubuzaiAPIException("objectType is required for SEARCH");
		if ($rest_key == null) throw new WuchubuzaiAPIException("rest_key is required for SEARCH");
		if ($attributes != null) if (!is_array($attributes)) throw new WuchubuzaiAPIException("attributes must be an array for SEARCH");
		return $this->sendPackage('SEARCH', $objectType, $objectId=null, $attributes, $rest_key);
	}

}

class WuchubuzaiAPIOutput {
	public $json;
	public $raw;
}

class WuchubuzaiAPIException extends Exception {}

?>
