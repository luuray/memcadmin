<?php

abstract class Memcadmin_Misc {

	static public function getMicrotimeFloat() {

	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	static public function formatSecAsmSec($sec) {

		return number_format($sec*1000, 2).'ms';
	}

	static public function dump($dump) {
		
		echo '<pre>'.print_r($dump, true).'</pre>';
	}

	static public function bsize($s) {
		foreach (array('','K','M','G') as $i => $k) {
			if ($s < 1024) break;
			$s/=1024;
		}
		return sprintf("%5.1f %sBytes",$s,$k);
	}
}