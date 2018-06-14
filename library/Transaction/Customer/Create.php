<?php namespace PHPBook\Payment\Transaction\Customer;

class Create extends \PHPBook\Payment\Transaction\Adapter {
    
    private $customer;

    public function setCustomer(\PHPBook\Payment\Customer $customer): Create {
    	$this->customer = $customer;
    	return $this;
    }

    public function getCustomer(): ?\PHPBook\Payment\Customer {
    	return $this->customer;
    }

    public function create() {
        
        $gateway = $this->getGateway();

        if (($gateway) and ($gateway->getDriver())) {

            try {

                $gateway->getDriver()->createCustomer($this->getCustomer());

            } catch(\Exception $e) {
                
                if ($gateway->getExceptionCatcher()) {

                    $gateway->getExceptionCatcher()($e->getMessage());

                };

            };

        };

    }
  
}
