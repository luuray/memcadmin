<?php

class Memcadmin_Controller {

	private $_structure = null;
	private $_view = null;
	private $_requestParams = array();

	public function __construct($requestParams = array(), $structure = null) {

		$this->_structure = $structure;
		$this->_requestParams = $requestParams;
	}

	public function __destruct() {

	}

	public function actionCluster() {

		$this->_view->clusterName = '';
		$this->_view->nodes = array();

		if (isset($this->_requestParams[0])) {
			foreach($this->_structure as $cluster) {
				if ($cluster->getName() == $this->_requestParams[0]) {

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