<?php

class Memcadmin_Application {

	private $_config = null;

	public function __construct($configFilename = null) {

		$this->_config = $this->_readConfig($configFilename);

		if ($this->_config)
			return true;

		return false;
	}

	public function __desctruct() {}

	public function init() {

		if ($this->_config) {

			dump($this->_config);
		}

		return $this;
	}

	public function run() {



		return $this;
	}

	private function _readConfig($filename = null) {

		$config = array();

		if ($filename)
			$config = parse_ini_file($filename, true);

		return $config;
	}
}