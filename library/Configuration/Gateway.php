<?php namespace PHPBook\Payment\Configuration;

class Gateway {
    
	private $name;

	private $exceptionCatcher;

	private $driver;

	public function getName(): String {
		return $this->name;
	}

	public function setName(String $name): Gateway {
		$this->name = $name;
		return $this;
	}

	public function getExceptionCatcher(): ?\Closure {
		return $this->exceptionCatcher;
	}

	public function setExceptionCatcher(\Closure $exceptionCatcher): Gateway {
		$this->exceptionCatcher = $exceptionCatcher;
		return $this;
	}

	public function getDriver(): ?\PHPBook\Payment\Driver\Adapter {
		return $this->driver;
	}

	public function setDriver(\PHPBook\Payment\Driver\Adapter $driver): Gateway {
		$this->driver = $driver;
		return $this;
	}

}