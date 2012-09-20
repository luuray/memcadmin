<?php

class Memcadmin_Controller {

	private $_structure = null;
	private $_view = null;
	private $_requestParams = array();
	private $_plain = false;

	public function __construct($requestParams = array(), $structure = null) {

		$this->_structure = $structure;
		$this->_requestParams = $requestParams;
	}

	public function __destruct() {

	}

	public function isPlain() {
		return $this->_plain;
	}

	private function setPlain() {
		$this->_plain = true;
	}


	private function _getCurHdl() {

		$requestClusterName = null;
		$requestNodeName = null;
		$hdl = array(
			'cluster' => null,
			'node' => null
		);

		if (isset($_GET['c']))
			$requestClusterName = $_GET['c'];
		if (isset($_GET['n']))
			$requestNodeName = $_GET['n'];

		if ($requestClusterName && $requestNodeName) {
			foreach($this->_structure as $cluster) {
				if ($cluster->getName() == $requestClusterName) {
					$hdl['cluster'] = $cluster;
					foreach($cluster->getNodes() as $nodeId => $node) {
						if ($node->getName() == $requestNodeName) {
							$hdl['node'] = $node;
							break;
						}
					}
					break;
				}
			}
		}

		return $hdl;			
	}

	public function actionSlabs() {

		$this->_view->slabs = null;
		$this->_view->clusterName = '';
		$this->_view->nodeName = '';
		$hdl = $this->_getCurHdl();
		$cluster = $hdl['cluster'];
		$node = $hdl['node'];



		if ($node) {
			$this->_view->clusterName = $cluster->getName();
			$this->_view->nodeName = $node->getName();

			$this->_view->slabs = Memcadmin_Memcache::getSlabs($node->getIp(), $node->getPort());

			foreach($this->_view->slabs['items'] as $slabId => $slab) {
				$this->_view->slabs['items'][$slabId]['evicted'] = ($slab['evicted'] == 1) ? 'Yes':'No';
				$this->_view->slabs['items'][$slabId]['age'] = Memcadmin_Misc::duration(time()-$slab['age']);
			}
		}
	}

	public function actionFlush() {
		
		$result = 'ERROR';
		$this->setPlain();
		$hdl = $this->_getCurHdl();
		$cluster = $hdl['cluster'];
		$node = $hdl['node'];
		
		if ($node) {
			$r = Memcadmin_Memcache::flush($node->getIp(), $node->getPort());
			if ($r == 'OK')
				$result = 'OK';
		}
		
		header('Content-type: application/json');
		echo json_encode(array('code' => $result));
	}

	public function actionCluster() {

		$this->_view->clusterName = '';
		$this->_view->nodes = array();
		$requestClusterName = null;

		if (isset($_GET['c']))
			$requestClusterName = $_GET['c'];

		if ($requestClusterName) {
			foreach($this->_structure as $cluster) {
				if ($cluster->getName() == $requestClusterName) {

					$clusterMemSize = 0;
					$clusterMemUsed = 0;

					foreach($cluster->getNodes() as $nodeId => $node) {

						$state = $node->getUpState() ? 'UP' : 'DOWN';
						$responseTime = Memcadmin_Misc::formatSecAsmSec($node->getLastResponseTime());
						$stats = $node->getFullStats();
						$version = $stats['version'];
						$startTime = time()-intval($stats['uptime']);
						$upTime = $stats['uptime'];
						$memSize = intval($stats['limit_maxbytes']);
						$memUsed = intval($stats['bytes']);

						$clusterMemSize += $memSize;
						$clusterMemUsed += $memUsed;

						$this->_view->nodes[] = array(
							'node_id' => $nodeId,
							'name' => $node->getName(),
							'ip' => $node->getIp(),
							'port' => $node->getPort(),
							'stats' => $stats,
							'responseTime' => $responseTime,
							'state' => $state,
							'startTime' => $startTime,
							'upTime' => $upTime,
							'memSize' => Memcadmin_Misc::bsize($memSize),
							'memUsed' => Memcadmin_Misc::bsize($memUsed),
							'memAvailable' => Memcadmin_Misc::bsize($memSize-$memUsed),
							'version' => $version
						);
					}

					$this->_view->cluster = array(
						'name' => $cluster->getName(),
						'memSize' => Memcadmin_Misc::bsize($clusterMemSize),
						'memUsed' => Memcadmin_Misc::bsize($clusterMemUsed),
						'memAvailable' => Memcadmin_Misc::bsize($clusterMemSize-$clusterMemUsed),
					);

					break;
				}
			}
		}

		return $this;
	}

	public function actionOverview() {

		$this->_view->structure = array();

		foreach($this->_structure as $cluster) {

			$this->_view->structure[$cluster->getName()] = array();
			$nodes = $cluster->getNodes();

			foreach($nodes as $node) {

				$this->_view->structure[$cluster->getName()][$node->getName()] = array(
					'ip' => $node->getIp(),
					'port' => $node->getPort(),
					'state' => $node->getUpState() ? 'UP' : 'DOWN',
					'responseTime' => Memcadmin_Misc::formatSecAsmSec($node->getLastResponseTime())
				);
			}
		}

		return $this;
	}

	public function meld($view) {

		include_once 'view/'.$view.'.phtml';
	}
}