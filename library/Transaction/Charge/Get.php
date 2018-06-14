<?php namespace PHPBook\Payment\Transaction\Charge;

class Get extends \PHPBook\Payment\Transaction\Adapter {
    
    private $token;

    public function setToken(String $token): Get {
    	$this->token = $token;
    	return $this;
    }

    public function getToken(): ?String {
    	return $this->token;
    }

    public function get(): ?\PHPBook\Payment\Charge {
        
        $gateway = $this->getGateway();

        if (($gateway) and ($gateway->getDriver())) {

            try {

                return $gateway->getDriver()->getCharge($this->getToken());
            
                    
            } catch(\Exception $e) {

                if ($gateway->getExceptionCatcher()) {

                    $gateway->getExceptionCatcher()($e->getMessage());
                    
                };

                return null;

            };
            
        };

    }
  
}
