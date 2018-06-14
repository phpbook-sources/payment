<?php namespace PHPBook\Payment\Transaction\Card;

class Create extends \PHPBook\Payment\Transaction\Adapter {
    
    private $customerToken;

    private $card;

    public function setCustomerToken(String $customerToken): Create {
    	$this->customerToken = $customerToken;
    	return $this;
    }

    public function getCustomerToken(): ?String {
    	return $this->customerToken;
    }

    public function setCard($number, $cvv, $name, $month, $year): Create {
        $this->card = [$number, $cvv, $name, $month, $year];
        return $this;
    }

    public function getCard(): Array {
        return $this->card;
    }

    public function create(): ?String {
        
        $gateway = $this->getGateway();

        if (($gateway) and ($gateway->getDriver())) {

            try {

                return $gateway->getDriver()->createCard($this->getCustomerToken(), $this->getCard());

            } catch(\Exception $e) {
                
                if ($gateway->getExceptionCatcher()) {

                    $gateway->getExceptionCatcher()($e->getMessage());

                };

                return [];

            };

        };

    }
  
}
