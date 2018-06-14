<?php namespace PHPBook\Payment\Transaction\Charge;

class Create extends \PHPBook\Payment\Transaction\Adapter {
    
    private $customer;

    private $cardToken;

    private $charge;

    public function setCustomer(\PHPBook\Payment\Customer $customer): Create {
    	$this->customer = $customer;
    	return $this;
    }

    public function getCustomer(): ?\PHPBook\Payment\Customer {
    	return $this->customer;
    }

    public function setCardToken(String $cardToken): Create {
    	$this->cardToken = $cardToken;
    	return $this;
    }

    public function getCardToken(): ?String {
    	return $this->cardToken;
    }

    public function setCharge(\PHPBook\Payment\Charge $charge): Create {
    	$this->charge = $charge;
    	return $this;
    }

    public function getCharge(): ?\PHPBook\Payment\Charge {
    	return $this->charge;
    }

    public function create() {
        
        $gateway = $this->getGateway();

        if (($gateway) and ($gateway->getDriver())) {

            try {
                
                $gateway->getDriver()->createCharge($this->getCustomer(), $this->getCardToken(), $this->getCharge());
                    
            } catch(\Exception $e) {

                if ($gateway->getExceptionCatcher()) {

                    $gateway->getExceptionCatcher()($e->getMessage());
                    
                };

            };

        };

    }
  
}
