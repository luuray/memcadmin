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

	static public function duration($ts) {
		$time = time();
		$years = (int)((($time - $ts)/(7*86400))/52.177457);
		$rem = (int)(($time-$ts)-($years * 52.177457 * 7 * 86400));
		$weeks = (int)(($rem)/(7*86400));
		$days = (int)(($rem)/86400) - $weeks*7;
		$hours = (int)(($rem)/3600) - $days*24 - $weeks*7*24;
		$mins = (int)(($rem)/60) - $hours*60 - $days*24*60 - $weeks*7*24*60;
		$str = '';
		if($years==1) $str .= "$years year, ";
		if($years>1) $str .= "$years years, ";
		if($weeks==1) $str .= "$weeks week, ";
		if($weeks>1) $str .= "$weeks weeks, ";
		if($days==1) $str .= "$days day,";
		if($days>1) $str .= "$days days,";
		if($hours == 1) $str .= " $hours hour and";
		if($hours>1) $str .= " $hours hours and";
		if($mins == 1) $str .= " 1 minute";
		else $str .= " $mins minutes";
		return $str;
	}

	static public function lifetime($ts)
	{
		$lifetime = $ts-time();
		$flag = 1;
		if($lifetime<0){
			$flag = -1;
			$lifetime = $lifetime * -1;
		}

		$day = intval($lifetime/86400.0);
		$day = $day>0?$day:0;
		$hour = intval(($lifetime-$day*86400)/3600);
		$hour = $hour>0?$hour:0;
		$min = intval(($lifetime-$day*86400-$hour*3600)/60.0);
		$min=$min>0?$min:0;
		$sec = $lifetime-$day*86400-$hour*3600-$min*60;

		return sprintf('%s %d d, %d h, %d m, %d s', $flag>0?'':'exp', $day, $hour, $min, $sec);
	}
}