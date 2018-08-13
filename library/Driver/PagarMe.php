<?php namespace PHPBook\Payment\Driver;

class PagarMe extends Adapter  {
    	
    private $key;

    private $keyVersion;

    public function getKey(): String {
    	return $this->key;
    }

    public function setKey(String $key): PagarMe {
    	$this->key = $key;
    	return $this;
    }

    public function getKeyVersion(): String {
    	return $this->keyVersion;
    }

    public function setKeyVersion(String $keyVersion): PagarMe {
    	$this->keyVersion = $keyVersion;
    	return $this;
    }

    private function getMetaIndex(String $meta): String {
        return str_replace(['+', '|', '-', '"', '*', '(', ')', '~N'], '', $meta);
    }

    private function checkCustomerDocsType(\PHPBook\Payment\Customer $customer): String {

        $documentType = 'custom';

        if (strpos($customer->getIdentity(), '.') === false) {
            if (strlen($customer->getIdentity()) == 11) {
                $documentType = 'cpf';
            } else {
                if (strlen($customer->getIdentity()) == 14) {
                    $documentType = 'cnpj';
                };
            };
        } else {
            if (strlen($customer->getIdentity()) == 14) {
                $documentType = 'cpf';
            } else {
                if (strlen($customer->getIdentity()) == 18) {
                    $documentType = 'cnpj';
                };
            };
        };

        return $documentType;

    }

    public function getChargeStatus(String $gatewayStatus): String {

        switch($gatewayStatus) {

            case 'paid':
                   return \PHPBook\Payment\Charge::$STATUS_COMPLETE;
                break;

            case 'processing':
            case 'authorized':
            case 'waiting_payment':
            case 'pending_refund':
                    return \PHPBook\Payment\Charge::$STATUS_WAITING;
                break;

            case 'refused':
                    return \PHPBook\Payment\Charge::$STATUS_DENY;
                break;

            case 'refunded':
                   return \PHPBook\Payment\Charge::$STATUS_REFUNDED;
                break;

            default:
                    return \PHPBook\Payment\Charge::$STATUS_IDLE;
                break;

        };

    }

    public function createCustomer(\PHPBook\Payment\Customer $customer) {

        switch($this->getKeyVersion()) {

            case 'v2017-07-17':
            case 'v2017-08-28':
                
                    $post = json_encode([
                        'api_key' => $this->getKey(),
                        'external_id' => $customer->getEmail(),
                        'name' => $customer->getName(),
                        'type' => 'individual',
                        'country' => 'br',
                        'email' => $customer->getEmail(),
                        'documents' => [['type' => $this->checkCustomerDocsType($customer), 'number' => $customer->getIdentity()]],
                        'phone_numbers' => ['+' . $customer->getPhoneCountry() . ' ' . $customer->getPhoneLocal() . ' ' . $customer->getPhone()]
                    ]);

                break;

            case 'v2013-03-01':

                    $post = json_encode([
                        'api_key' => $this->getKey(),
                        'external_id' => null,
                        'name' => $customer->getName(),
                        'type' => 'individual',
                        'country' => 'br',
                        'email' => $customer->getEmail(),
                        'document_type' => 'custom',
                        'document_number' => $customer->getIdentity(),
                        'phone' => ['ddi' => $customer->getPhoneCountry(), 'ddd' => $customer->getPhoneLocal(), 'number' => $customer->getPhone()]
                    ]);
                    
                break;

            default: 
                    throw new \Exception('Invalid PagarMe API Version');
                break;
        };

        $post = utf8_encode($post);

        $session = curl_init('https://api.pagar.me/1/customers');

        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);

        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
            
        curl_close($session);

        if ($httpcode == '200') {

            $item = json_decode($response);

            if ($item->id) {

                $customer->setToken($item->id);

            };
            
        } else {

            throw new \Exception($response);

        };

    }

    public function getCustomer(String $token): ?\PHPBook\Payment\Customer {

        $session = curl_init('https://api.pagar.me/1/customers/' . $token);

        $post = json_encode([
            'api_key' => $this->getKey()
        ]);

        $post = utf8_encode($post);

        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_POST, false);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);

        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

        curl_close($session);
        
        if ($httpcode == '200') {

            $item = json_decode($response);
            
            switch($this->getKeyVersion()) {

                case 'v2017-07-17':
                case 'v2017-08-28':

                    list($ddi, $ddd, $phone) = explode(' ', ltrim($item->phone_numbers[0], '+'));

                    return (new \PHPBook\Payment\Customer)
                        ->setToken($item->id)
                        ->setName($item->name)
                        ->setEmail($item->email)
                        ->setIdentity($item->documents[0]->number)
                        ->setPhone($phone)
                        ->setPhoneLocal($ddd)
                        ->setPhoneCountry($ddi);
                        
                    break;

                case 'v2013-03-01':

                    return (new \PHPBook\Payment\Customer)
                        ->setToken($item->id)
                        ->setName($item->name)
                        ->setEmail($item->email)
                        ->setIdentity($item->document_number)
                        ->setPhone($item->phones[0]->number)
                        ->setPhoneLocal($item->phones[0]->ddd)
                        ->setPhoneCountry($item->phones[0]->ddi);
                        
                    break;

                default: 
                    throw new \Exception('Invalid PagarMe API Version');
                break;

            };

        } else {

            throw new \Exception($response);
            
        };
        
        return null;

    }

    public function createCard(String $customerToken, Array $card): ?String {

        list($cardNumber, $cardCvv, $cardName, $cardMonth, $cardYear) = $card;

        $post = json_encode([
            'api_key' => $this->getKey(),
            'card_number' => $cardNumber,
            'card_expiration_date' => (strlen($cardMonth) == 1 ? '0' . $cardMonth : $cardMonth) . (strlen($cardYear) == 4 ? substr($cardYear, 2, 2) : $cardYear),
            'card_cvv' => $cardCvv,
            'card_holder_name' => $cardName,
            'customer_id' => $customerToken
        ]);

        $post = utf8_encode($post);
            
        $session = curl_init('https://api.pagar.me/1/cards');

        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);

        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

        curl_close($session);

        if ($httpcode == '200') {

            $item = json_decode($response);

            if ($item->id) {

                return $item->id;

            };

        } else {

            throw new \Exception($response);
            
        };

        return null;

    }

    public function createCharge(\PHPBook\Payment\Customer $customer, String $cardToken, \PHPBook\Payment\Charge $charge) {

        switch($this->getKeyVersion()) {

            case 'v2017-07-17':
            case 'v2017-08-28':

                    $post = json_encode([
                        'api_key' => $this->getKey(),
                        'amount' => $charge->getPriceCents(),
                        'card_id' => $cardToken,
                        'payment_method' => 'credit_card',
                        'customer' => [
                            'id' => $customer->getToken(),
                            'external_id' => $customer->getEmail(),
                            'name' => $customer->getName(),
                            'type' => 'individual',
                            'country' => 'br',
                            'email' => $customer->getEmail(),
                            'documents' => [['type' =>  $this->checkCustomerDocsType($customer), 'number' => $customer->getIdentity()]],
                            'phone_numbers' => ['+' . $customer->getPhoneCountry() . ' ' . $customer->getPhoneLocal() . ' ' . $customer->getPhone()]
                        ],
                        'metadata' => [
                            'references' => $charge->getMeta(), 
                            'references_index' => $this->getMetaIndex($charge->getMeta()),
                            'country' => $charge->getShippingAddressCountry()
                        ],
                        'billing' => [
                            "name" => $customer->getName(),
                            "address" => [
                                "country" => strtolower($charge->getShippingAddressCountry()),
                                "state" => $charge->getShippingAddressState(),
                                "city" => $charge->getShippingAddressCity(),
                                "neighborhood" => $charge->getShippingAddressNeighborhood(),
                                "street" => $charge->getShippingAddressStreet(),
                                "street_number" => $charge->getShippingAddressNumber(),
                                "zipcode" => $charge->getShippingAddressZipCode()
                            ]
                        ],
                        'items' => [
                            [
                                "id" => "001",
                                "title" => "Service",
                                "unit_price" => $charge->getPriceCents(),
                                "quantity" => 1,
                                "tangible" => true
                            ]
                        ]
                    ]);

                break;

            case 'v2013-03-01':

                    $post = json_encode([
                        'api_key' => $this->getKey(),
                        'amount' => $charge->getPriceCents(),
                        'card_id' => $cardToken,
                        'payment_method' => 'credit_card',
                        'customer' => [
                            'id' => $customer->getToken(),
                            'external_id' => null,
                            'name' => $customer->getName(),
                            'type' => 'individual',
                            'country' => 'br',
                            'email' => $customer->getEmail(),
                            'document_type' => 'custom',
                            'address' => [
                                'street' => $charge->getShippingAddressStreet(),
                                'street_number' => $charge->getShippingAddressNumber(),
                                'neighborhood' => $charge->getShippingAddressNeighborhood(),
                                'zipcode' => $charge->getShippingAddressZipCode(),
                                'city' => $charge->getShippingAddressCity(),
                                'state' => $charge->getShippingAddressState(),
                                'country' => $charge->getShippingAddressCountry()
                            ],
                            'document_number' => $customer->getIdentity(),
                            'phone' => ['ddi' => $customer->getPhoneCountry(), 'ddd' => $customer->getPhoneLocal(), 'number' => $customer->getPhone()]
                        ],
                        'metadata' => [
                            'references' => $charge->getMeta(), 
                            'references_index' => $this->getMetaIndex($charge->getMeta()),
                            'street' => $charge->getShippingAddressStreet(),
                            'street_number' => $charge->getShippingAddressNumber(),
                            'neighborhood' => $charge->getShippingAddressNeighborhood(),
                            'zipcode' => $charge->getShippingAddressZipCode(),
                            'city' => $charge->getShippingAddressCity(),
                            'state' => $charge->getShippingAddressState(),
                            'country' => $charge->getShippingAddressCountry()
                        ]
                    ]);                    

                break;

            default: 
                throw new \Exception('Invalid PagarMe API Version');
            break;
        };

        $post = utf8_encode($post);

        $session = curl_init('https://api.pagar.me/1/transactions');

        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);

        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

        curl_close($session);

        if ($httpcode == '200') {

            $item = json_decode($response);

            if ($item->id) {

                $charge->setToken($item->id);

                $charge->setStatus($this->getChargeStatus($item->status));

            };

        } else {

            throw new \Exception($response);
            
        };
            
    }

    public function refundCharge(\PHPBook\Payment\Charge $charge) {

        $post = json_encode([
            'api_key' => $this->getKey()
        ]);

        $post = utf8_encode($post);
            
        $session = curl_init('https://api.pagar.me/1/transactions/' . $charge->getToken() . '/refund');

        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8',
        ]);

        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);

        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

        curl_close($session);
        
        if ($httpcode == '200') {

            $item = json_decode($response);

            if ($item->id) {

                $charge->setStatus($this->getChargeStatus($item->status));

            };

        } else {

            throw new \Exception($response);
            
        };

    }

    public function getCharge(String $token): ?\PHPBook\Payment\Charge {

        $session = curl_init('https://api.pagar.me/1/transactions/' . $token);
    
        $post = json_encode([
          'api_key' => $this->getKey()
        ]);
    
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8',
        ]);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($session);
    
        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
    
        curl_close($session);
        
        if ($httpcode == '200') {

            $item = json_decode($response);

            if ($item->id) {

                switch($this->getKeyVersion()) {

                    case 'v2017-07-17':
                    case 'v2017-08-28':
                    
                        return (new \PHPBook\Payment\Charge)
                            ->setToken($item->id)
                            ->setPriceCents($item->amount)
                            ->setMeta(isset($item->metadata->references) ? $item->metadata->references : '')
                            ->setStatus($this->getChargeStatus($item->status))
                            ->setShippingAddressStreet($item->billing->address->street)
                            ->setShippingAddressNumber($item->billing->address->street_number)
                            ->setShippingAddressNeighborhood($item->billing->address->neighborhood)
                            ->setShippingAddressZipCode($item->billing->address->zipcode)
                            ->setShippingAddressCity($item->billing->address->city)
                            ->setShippingAddressState($item->billing->address->state)
                            ->setShippingAddressCountry(isset($item->metadata->country) ? $item->metadata->country : '');

                        break;

                    case 'v2013-03-01':

                        return (new \PHPBook\Payment\Charge)
                            ->setToken($item->id)
                            ->setPriceCents($item->amount)
                            ->setMeta(isset($item->metadata->references) ? $item->metadata->references : '')
                            ->setStatus($this->getChargeStatus($item->status))
                            ->setShippingAddressStreet(isset($item->metadata->street) ? $item->metadata->street : '')
                            ->setShippingAddressNumber(isset($item->metadata->street_number) ? $item->metadata->street_number : '')
                            ->setShippingAddressNeighborhood(isset($item->metadata->neighborhood) ? $item->metadata->neighborhood : '')
                            ->setShippingAddressZipCode(isset($item->metadata->zipcode) ? $item->metadata->zipcode : '')
                            ->setShippingAddressCity(isset($item->metadata->city) ? $item->metadata->city : '')
                            ->setShippingAddressState(isset($item->metadata->state) ? $item->metadata->state : '')
                            ->setShippingAddressCountry(isset($item->metadata->country) ? $item->metadata->country : '');
                        
                        break;

                };                   

            };

        } else {

            throw new \Exception($response);
            
        };
        
        return null;

    }

    public function getChargesByMeta(String $meta): Array { # Array of \PHPBook\Payment\Charge

        $charges = [];

        $page = 0;

        $size = 10;

        $hits = 0;

        do {

            $session = curl_init('https://api.pagar.me/1/search');

            $post = json_encode([
                'api_key' => $this->getKey(),
                'type' => 'transaction',
                'query' => [
                    'query' => [
                        'terms' => [
                            'metadata.references_index' => [$this->getMetaIndex($meta)]
                        ],
                    ],
                    "sort" => [[
                        "date_created" => ["order" => "asc"]
                    ]],
                    'size' => $size,
                    'from' => $page * $size
                ]
            ]);

            curl_setopt($session, CURLOPT_HTTPHEADER, [
                'accept:application/json',
                'content-type:application/json; charset=utf-8',
            ]);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_POSTFIELDS, $post);
            curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($session);

            $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

            curl_close($session);

            if ($httpcode == '200') {
                
                $items = json_decode($response);

                $hits = $items->hits->total;

                if (count($items->hits->hits)) {

                    foreach($items->hits->hits as $item) {

                        switch($this->getKeyVersion()) {

                            case 'v2017-07-17':
                            case 'v2017-08-28':

                                $charges[] = (new \PHPBook\Payment\Charge)
                                    ->setToken($item->_source->id)
                                    ->setPriceCents($item->_source->amount)
                                    ->setMeta(isset($item->_source->metadata->references) ? $item->_source->metadata->references : '')
                                    ->setStatus($this->getChargeStatus($item->_source->status))
                                    ->setShippingAddressStreet($item->_source->billing->address->street)
                                    ->setShippingAddressNumber($item->_source->billing->address->street_number)
                                    ->setShippingAddressNeighborhood($item->_source->billing->address->neighborhood)
                                    ->setShippingAddressZipCode($item->_source->billing->address->zipcode)
                                    ->setShippingAddressCity($item->_source->billing->address->city)
                                    ->setShippingAddressState($item->_source->billing->address->state)
                                    ->setShippingAddressCountry(isset($item->_source->metadata->country) ? $item->_source->metadata->country : '');
                        
                                break;
                        
                            case 'v2013-03-01':

                                $charges[] = (new \PHPBook\Payment\Charge)
                                    ->setToken($item->_source->id)
                                    ->setPriceCents($item->_source->amount)
                                    ->setMeta(isset($item->_source->metadata->references) ? $item->_source->metadata->references : '')
                                    ->setStatus($this->getChargeStatus($item->_source->status))
                                    ->setShippingAddressStreet(isset($item->_source->metadata->street) ? $item->_source->metadata->street : '')
                                    ->setShippingAddressNumber(isset($item->_source->metadata->street_number) ? $item->_source->metadata->street_number : '')
                                    ->setShippingAddressNeighborhood(isset($item->_source->metadata->neighborhood) ? $item->_source->metadata->neighborhood : '')
                                    ->setShippingAddressZipCode(isset($item->_source->metadata->zipcode) ? $item->_source->metadata->zipcode : '')
                                    ->setShippingAddressCity(isset($item->_source->metadata->city) ? $item->_source->metadata->city : '')
                                    ->setShippingAddressState(isset($item->_source->metadata->state) ? $item->_source->metadata->state : '')
                                    ->setShippingAddressCountry(isset($item->_source->metadata->country) ? $item->_source->metadata->country : '');
                                
                                break;
                        
                        };   

                    };

                } else {

                    break;

                };

                $page++;

            } else {

                throw new \Exception($response);
                
            };
            

        } while(true);

        return $charges;

    }
    
}