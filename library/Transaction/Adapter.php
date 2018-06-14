<?php namespace PHPBook\Payment\Transaction;

abstract class Adapter {
    
    private $gatewayCode;

    public function setGatewayCode(String $gatewayCode): Adapter {
    	$this->gatewayCode = $gatewayCode;
    	return $this;
    }

    public function getGatewayCode(): ?String {
    	return $this->gatewayCode;
    }

    public function getGateway(): ?\PHPBook\Payment\Configuration\Gateway {
        return \PHPBook\Payment\Configuration\Payment::getGateway($this->getGatewayCode());
    }
  
}
