<?php namespace PHPBook\Payment\Transaction\Charge\Meta;

class Get extends \PHPBook\Payment\Transaction\Adapter {
    
    private $meta;

    public function setMeta(String $meta): Get {
    	$this->meta = $meta;
    	return $this;
    }

    public function getMeta(): ?String {
    	return $this->meta;
    }

    public function get(): Array {
        
        $gateway = $this->getGateway();

        if (($gateway) and ($gateway->getDriver())) {

            try {

                return $gateway->getDriver()->getChargesByMeta($this->getMeta());
            
                    
            } catch(\Exception $e) {

                if ($gateway->getExceptionCatcher()) {

                    $gateway->getExceptionCatcher()($e->getMessage());
                    
                };

                return [];

            };
            
        };

    }
  
}
