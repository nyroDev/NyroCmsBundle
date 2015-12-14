<?php

namespace NyroDev\NyroCmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use NyroDev\NyroCmsBundle\Model\Composable;

class WrapperCssThemeEvent extends Event {
	
	protected $row,
				$wrapperCssTheme;
	
	public function __construct(Composable $row) {
		$this->row = $row;
	}
	
	/**
	 * 
	 * @return Composable
	 */
	public function getRow() {
		return $this->row;
	}
	
	public function setWrapperCssTheme($wrapperCssTheme) {
		$this->wrapperCssTheme = $wrapperCssTheme;
	}
	
	public function getWrapperCssTheme() {
		return $this->wrapperCssTheme;
	}
	
}