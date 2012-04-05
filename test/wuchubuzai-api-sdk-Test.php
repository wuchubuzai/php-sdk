<?php
/**
 * Unit test for the Wuchubuzai PHP-SDK
 *
 * @version 2012.04.02
 * @license See the included LICENSE file for more information [ https://github.com/wuchubuzai/php-sdk/LICENSE ]
 * @link http://www.wuchubuzai.com/
 * Twitter information:  https://twitter.com/wuchubuzai_labs
 *                       https://twitter.com/wuchubuzai_dev
 */
class testWuchuApiGET extends PHPUnit_Framework_TestCase {

	private static $sdk = null;
	private static $restKey = null;
	private static $uid = null;

	private $testUser = '';
	private $testUserPass = '';
	private $debug = false;

	protected function setUp() {
		require_once dirname(__FILE__) . '/../src/wuchubuzai-php-sdk.php';
		if (self::$sdk == null) {
			self::$sdk = new WuchubuzaiAPI();
			if ($this->debug) self::$sdk->enableDebug();
		}
	}

	public function testConfiguration() {
		$this->assertEquals('http://dev.api.wuchubuzai.com', self::$sdk->getApiUrl());
	}

	public function testGetWithNoRestKey() {
		self::$sdk->get("content", "foobar");
		$this->assertEquals('API_ERROR_000002', self::$sdk->output->json['error_code']);
	}

	public function testPOSTUserAuthentication() {
		self::$sdk->post("accounts", array('email' => $this->testUser, 'password' => $this->testUserPass));
		$this->assertEquals("API_SUCCESS_000002", self::$sdk->output->json['success_code']);
 		self::$restKey = self::$sdk->output->json['rest_key'];
 		self::$uid = self::$sdk->output->json['id'];
	}

	public function testGetWithRestKey() {
		if (self::$uid != null && self::$restKey != null) {
			self::$sdk->get("user", self::$uid, $rest_key=self::$restKey);
			$this->assertEquals(self::$uid, self::$sdk->output->json['uid']);
		} else {
			$this->markTestIncomplete("invalid rest key / uid");
			var_dump($this);
		}
	}

	public function testInteractions() {
		if (self::$uid != null && self::$restKey != null) {
			try {
				self::$sdk->post("interactions", array('rest_key' => self::$restKey, 'uid' => self::$uid, 'method' => 'load_session'));
				$this->assertEquals(self::$uid . '@chat.wuchubuzai.com/wuchubuzai_api', self::$sdk->output->json['jid']);
			} catch (WuchubuzaiAPIException $e) {
				// indicates that the xmpp
				$this->assertEquals('empty output from cURL', $e->getMessage());
			}
		} else {
			$this->markTestIncomplete("invalid rest key / uid");
			var_dump($this);
		}
	}

	/**
	 * Testing SEARCH
	 */

	public function testSearchWithoutRestKey() {
		try {
			self::$sdk->search("user", null, null);
		} catch (WuchubuzaiAPIException $e) {
			$this->assertEquals('rest_key is required for SEARCH', $e->getMessage());
		}
	}

	public function testSearchWithRestKeyAndNullAttributes() {
		if (self::$uid != null && self::$restKey != null) {
			try {
				self::$sdk->search("user", self::$restKey, null);
			} catch (WuchubuzaiAPIException $e) {
				$this->assertEquals('attributes are required for SEARCH', $e->getMessage());
			}
		} else {
			$this->markTestIncomplete("invalid rest key / uid");
			var_dump($this);
		}
	}

	public function testSearchWithRestKeyAndAttributes() {
		if (self::$uid != null && self::$restKey != null) {
			self::$sdk->search("user", self::$restKey, array('gender' => 2, 'seeking_gender' => 1));
			if (isset(self::$sdk->output->json['error'])) {
				$this->assertEquals('SearchAPIException', self::$sdk->output->json['error']);
			} else {
				$this->assertTrue(true);
			}
		} else {
			$this->markTestIncomplete("invalid rest key / uid");
			var_dump($this);
		}
	}

}

?>