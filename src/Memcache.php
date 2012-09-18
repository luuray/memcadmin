<?php

abstract class Memcadmin_Memcache {

	static private $_lastResponseTime;

	public function getLastResponseTime() {

		return self::$_lastResponseTime;
	}

	public static function testConnection($server, $port) {

		$startTime = Memcadmin_Misc::getMicrotimeFloat();
		$s = @fsockopen($server,$port, $errno, $errstr, 3); // wait max. 3 seconds
		self::$_lastResponseTime = Memcadmin_Misc::getMicrotimeFloat()-$startTime;

		if (!$s)
			return false;

		return true;
	}

	public static function sendCommand($server, $port, $command) {

		$s = @fsockopen($server,$port);
		if (!$s)
			return '';

		fwrite($s, $command."\r\n");

		$buf='';
		while ((!feof($s))) {
			$buf .= fgets($s, 256);
			if (strpos($buf,"END\r\n")!==false){ // stat says end
			    break;
			}
			if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
			    break;
			}
			if (strpos($buf,"OK\r\n")!==false){ // flush_all says ok
			    break;
			}
		}
	    fclose($s);

	    return self::_parseMemcacheResults($buf);
	}

	private static function _parseMemcacheResults($str) {
	    
		$res = array();
		$lines = explode("\r\n",$str);
		$cnt = count($lines);
		for($i=0; $i< $cnt; $i++){
		    $line = $lines[$i];
			$l = explode(' ',$line,3);
			if (count($l)==3){
				$res[$l[0]][$l[1]]=$l[2];
				if ($l[0]=='VALUE'){ // next line is the value
				    $res[$l[0]][$l[1]] = array();
				    list ($flag,$size)=explode(' ',$l[2]);
				    $res[$l[0]][$l[1]]['stat']=array('flag'=>$flag,'size'=>$size);
				    $res[$l[0]][$l[1]]['value']=$lines[++$i];
				}
			}elseif($line=='DELETED' || $line=='NOT_FOUND' || $line=='OK'){
			    return $line;
			}
		}
		return $res;

	}

/*
	function sendMemcacheCommands($command){
	    global $MEMCACHE_SERVERS;
		$result = array();

		foreach($MEMCACHE_SERVERS as $server){
			$strs = explode(':',$server);
			$host = $strs[0];
			$port = $strs[1];
			$result[$server] = sendMemcacheCommand($host,$port,$command);
		}
		return $result;
	}
	


	function dumpCacheSlab($server,$slabId,$limit){
	    list($host,$port) = explode(':',$server);
	    $resp = sendMemcacheCommand($host,$port,'stats cachedump '.$slabId.' '.$limit);

	   return $resp;

	}

	function flushServer($server){
	    list($host,$port) = explode(':',$server);
	    $resp = sendMemcacheCommand($host,$port,'flush_all');
	    return $resp;
	}
	function getCacheItems(){
	 $items = sendMemcacheCommands('stats items');
	 $serverItems = array();
	 $totalItems = array();
	 foreach ($items as $server=>$itemlist){
	    $serverItems[$server] = array();
	    $totalItems[$server]=0;
	    if (!isset($itemlist['STAT'])){
	        continue;
	    }

	    $iteminfo = $itemlist['STAT'];

	    foreach($iteminfo as $keyinfo=>$value){
	        if (preg_match('/items\:(\d+?)\:(.+?)$/',$keyinfo,$matches)){
	            $serverItems[$server][$matches[1]][$matches[2]] = $value;
	            if ($matches[2]=='number'){
	                $totalItems[$server] +=$value;
	            }
	        }
	    }
	 }
	 return array('items'=>$serverItems,'counts'=>$totalItems);
	}
	function getMemcacheStats($total=true){
		$resp = sendMemcacheCommands('stats');
		if ($total){
			$res = array();
			foreach($resp as $server=>$r){
				foreach($r['STAT'] as $key=>$row){
					if (!isset($res[$key])){
						$res[$key]=null;
					}
					switch ($key){
						case 'pid':
							$res['pid'][$server]=$row;
							break;
						case 'uptime':
							$res['uptime'][$server]=$row;
							break;
						case 'time':
							$res['time'][$server]=$row;
							break;
						case 'version':
							$res['version'][$server]=$row;
							break;
						case 'pointer_size':
							$res['pointer_size'][$server]=$row;
							break;
						case 'rusage_user':
							$res['rusage_user'][$server]=$row;
							break;
						case 'rusage_system':
							$res['rusage_system'][$server]=$row;
							break;
						case 'curr_items':
							$res['curr_items']+=$row;
							break;
						case 'total_items':
							$res['total_items']+=$row;
							break;
						case 'bytes':
							$res['bytes']+=$row;
							break;
						case 'curr_connections':
							$res['curr_connections']+=$row;
							break;
						case 'total_connections':
							$res['total_connections']+=$row;
							break;
						case 'connection_structures':
							$res['connection_structures']+=$row;
							break;
						case 'cmd_get':
							$res['cmd_get']+=$row;
							break;
						case 'cmd_set':
							$res['cmd_set']+=$row;
							break;
						case 'get_hits':
							$res['get_hits']+=$row;
							break;
						case 'get_misses':
							$res['get_misses']+=$row;
							break;
						case 'evictions':
							$res['evictions']+=$row;
							break;
						case 'bytes_read':
							$res['bytes_read']+=$row;
							break;
						case 'bytes_written':
							$res['bytes_written']+=$row;
							break;
						case 'limit_maxbytes':
							$res['limit_maxbytes']+=$row;
							break;
						case 'threads':
							$res['rusage_system'][$server]=$row;
							break;
					}
				}
			}
			return $res;
		}
		return $resp;
	}*/
}