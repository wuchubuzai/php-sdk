<?php

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
		$this->assertEquals('http://sbx.api.wuchubuzai.com', self::$sdk->getApiUrl());
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
			self::$sdk->post("interactions", array('rest_key' => self::$restKey, 'uid' => self::$uid, 'method' => 'load_session'));
			$this->assertEquals(self::$uid . '@chat.wuchubuzai.com', self::$sdk->output->json['jid']);
		} else {
			$this->markTestIncomplete("invalid rest key / uid");
			var_dump($this);
		}
	}


}

?>