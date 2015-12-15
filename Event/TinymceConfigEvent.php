<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use NyroDev\NyroCmsBundle\Model\Composable;

class TinymceConfigEvent extends Event {
	
	protected $row,
				$simple,
				$config;
	
	public function __construct(Composable $row, $simple, array $config) {
		$this->row = $row;
		$this->simple = $simple;
		$this->config = $config;
	}
	
	/**
	 * 
	 * @return Composable
	 */
	public function getRow() {
		return $this->row;
	}
	
	public function getSimple() {
		return $this->simple;
	}
	
	public function setConfig($config) {
		$this->config = $config;
	}
	
	public function getConfig() {
		return $this->config;
	}
	
}