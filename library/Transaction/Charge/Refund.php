<?php namespace PHPBook\Payment\Transaction\Charge;

class Refund extends \PHPBook\Payment\Transaction\Adapter {
    
    private $charge;

    public function setCharge(\PHPBook\Payment\Charge $charge): Refund {
    	$this->charge = $charge;
    	return $this;
    }

    public function getCharge(): ?\PHPBook\Payment\Charge {
    	return $this->charge;
    }

    public function refund() {
        
        $gateway = $this->getGateway();

        if (($gateway) and ($gateway->getDriver())) {

            try {
                
                $gateway->getDriver()->refundCharge($this->getCharge());
                    
            } catch(\Exception $e) {

                if ($gateway->getExceptionCatcher()) {

                    $gateway->getExceptionCatcher()($e->getMessage());
                    
                };

            };

        };

    }
  
}
