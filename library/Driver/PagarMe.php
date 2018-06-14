<?php namespace PHPBook\Payment\Driver;

class PagarMe extends Adapter  {
    	
    private $key;

    public function getKey(): String {
    	return $this->key;
    }

    public function setKey(String $key): PagarMe {
    	$this->key = $key;
    	return $this;
    }

    private function getMetaIndex(String $meta): String {
        return str_replace(['+', '|', '-', '"', '*', '(', ')', '~N'], '', $meta);
    }

    public function getChargeStatus(String $gatewayStatus): String {

        switch($gatewayStatus) {

            case \PagarMe\Sdk\Transaction\AbstractTransaction::PAID:
                   return \PHPBook\Payment\Charge::$STATUS_COMPLETE;
                break;

            case \PagarMe\Sdk\Transaction\AbstractTransaction::PROCESSING:
            case \PagarMe\Sdk\Transaction\AbstractTransaction::AUTHORIZED:
            case \PagarMe\Sdk\Transaction\AbstractTransaction::WAITING_PAYMENT:
            case \PagarMe\Sdk\Transaction\AbstractTransaction::PENDING_REFUND:
                    return \PHPBook\Payment\Charge::$STATUS_WAITING;
                break;

            case \PagarMe\Sdk\Transaction\AbstractTransaction::REFUSED:
                    return \PHPBook\Payment\Charge::$STATUS_DENY;
                break;

            case \PagarMe\Sdk\Transaction\AbstractTransaction::REFUNDED:
                   return \PHPBook\Payment\Charge::$STATUS_REFUNDED;
                break;

            default:
                    return \PHPBook\Payment\Charge::$STATUS_IDLE;
                break;

        };

    }

    public function createCustomer(\PHPBook\Payment\Customer $customer) {

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());

        $create = $pagarMe->customer()->create(
            $customer->getName(), 
            $customer->getEmail(), 
            $customer->getIdentity(), 
            new \PagarMe\Sdk\Customer\Address([
                    'street' => $customer->getAddressStreet(),
                    'streetNumber' => $customer->getAddressNumber(),
                    'neighborhood' => $customer->getAddressNeighborhood(),
                    'zipcode' => $customer->getAddressZipCode(),
                    'city' => $customer->getAddressCity(),
                    'state' => $customer->getAddressState(),
                    'country' => $customer->getAddressCountry(),
                ]), 
            new \PagarMe\Sdk\Customer\Phone([
                'number' => $customer->getPhone(),
                'ddd' => $customer->getPhoneLocal()
            ]), null, null);

        if ($create->getId()) {

            $customer->setToken($create->getId());

        };

    }

    public function getCustomer(String $token): ?\PHPBook\Payment\Customer {

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());

        $get = $pagarMe->customer()->get($token);

        if ($get) {

            return (new \PHPBook\Payment\Customer)
                ->setToken($get->getId())
                ->setName($get->getName())
                ->setEmail($get->getEmail())
                ->setIdentity($get->getDocumentNumber())
                ->setPhone($get->getPhone()->getNumber())
                ->setPhoneLocal($get->getPhone()->getDdd())
                ->setAddressStreet($get->getAddress()->getStreet())
                ->setAddressNumber($get->getAddress()->getStreetNumber())
                ->setAddressNeighborhood($get->getAddress()->getNeighborhood())
                ->setAddressZipCode($get->getAddress()->getZipcode())
                ->setAddressCity($get->getAddress()->getCity())
                ->setAddressState($get->getAddress()->getState())
                ->setAddressCountry($get->getAddress()->getCountry());

        };

        return null;

    }

    public function createCard(String $customerToken, Array $card): ?String {

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());

        list($cardNumber, $cardCvv, $cardName, $cardMonth, $cardYear) = $card;

        $create = $pagarMe->card()->create(
            $cardNumber,
            $cardName,
            (strlen($cardMonth) == 1 ? '0' . $cardMonth : $cardMonth) . 
            (strlen($cardYear) == 4 ? substr($cardYear, 2, 2) : $cardYear)
        );

        if ($create->getId()) {

            return $create->getId();

        };

        return null;

    }

    public function createCharge(\PHPBook\Payment\Customer $customer, String $cardToken, \PHPBook\Payment\Charge $charge) {

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());

        if ($customer->getToken()) {
            
            $transaction = $pagarMe->transaction()->creditCardTransaction(
                $charge->getPriceCents(),
                $pagarMe->card()->get($cardToken),
                new \PagarMe\Sdk\Customer\Customer([
                    'id' => $customer->getToken(),
                    'name' => $customer->getName(),
                    'email' => $customer->getEmail(),
                    'documentNumber' => $customer->getIdentity(),
                    'phone' => new \PagarMe\Sdk\Customer\Phone([
                        'ddd' => $customer->getPhoneLocal(),
                        'number' => $customer->getPhone()
                    ]),
                    'address' => new \PagarMe\Sdk\Customer\Address([
                        'street' => $customer->getAddressStreet(),
                        'streetNumber' => $customer->getAddressNumber(),
                        'neighborhood' => $customer->getAddressNeighborhood(),
                        'zipcode' => $customer->getAddressZipCode(),
                        'city' => $customer->getAddressCity(),
                        'state' => $customer->getAddressState(),
                        'country' => $customer->getAddressCountry(),
                    ]),
                ]),
                1,
                true,
                null,
                ['references' => $charge->getMeta(), 'references_index' => $this->getMetaIndex($charge->getMeta())]
            );

            if ($transaction->getId()) {

                $charge->setToken($transaction->getId());

                $charge->setStatus($this->getChargeStatus($transaction->getStatus()));

            };

        };
            
    }

    public function refundCharge(\PHPBook\Payment\Charge $charge) {

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());
    
        if ($charge->getToken()) {

            $pagarMe->transaction()->creditCardRefund(
                $pagarMe->transaction()->get($charge->getToken()),
                null
            );
    
            $transaction = $pagarMe->transaction()->get($charge->getToken());
    
            $charge->setStatus($this->getChargeStatus($transaction->getStatus()));
    
        };

    }

    public function getCharge(String $token): ?\PHPBook\Payment\Charge {

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());

        $transaction = $pagarMe->transaction()->get($token);

        if ($transaction) {

            return (new \PHPBook\Payment\Charge)
                ->setToken($transaction->getId())
                ->setPriceCents($transaction->getAmount())
                ->setMeta($transaction->getMetadata()['references'])
                ->setStatus($this->getChargeStatus($transaction->getStatus()));
                
        };
       
        return null;

    }

    public function getChargesByMeta(String $meta): Array { # Array of \PHPBook\Payment\Charge

        $pagarMe = new \PagarMe\Sdk\PagarMe($this->getKey());
        
        $results = $pagarMe->search()->get(
            'transaction',
            [   
                'query' => [
                    'query_string' => [
                        'query' => $this->getMetaIndex($meta),
                        'fields' => ['references_index'],
                        "default_operator" => "AND",
                    ],
                ]

            ]
        );

        $charges = [];
        
        foreach($results->hits->hits as $item) {
            $charges[] = (new \PHPBook\Payment\Charge)
                ->setToken($item->_source->id)
                ->setPriceCents($item->_source->amount)
                ->setMeta($item->_source->metadata->references)
                ->setStatus($this->getChargeStatus($item->_source->status));
        };

        return $charges;

    }
    
}