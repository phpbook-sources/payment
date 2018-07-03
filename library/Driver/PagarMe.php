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

        $post = json_encode([
            'api_key' => $this->getKey(),
            'external_id' => null,
            'name' => $customer->getName(),
            'type' => 'individual',
            'country' => 'BR',
            'address' => [
                'street' => $customer->getAddressStreet(),
                'street_number' => $customer->getAddressNumber(),
                'neighborhood' => $customer->getAddressNeighborhood(),
                'zipcode' => $customer->getAddressZipCode(),
                'city' => $customer->getAddressCity(),
                'state' => $customer->getAddressState(),
                'country' => $customer->getAddressCountry()
            ],
            'email' => $customer->getEmail(),
            'document_type' => 'cpf',
            'document_number' => $customer->getIdentity(),
            'phone' => ['ddd' => $customer->getPhoneLocal(), 'number' => $customer->getPhone()]
        ]);

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

            return (new \PHPBook\Payment\Customer)
                ->setToken($item->id)
                ->setName($item->name)
                ->setEmail($item->email)
                ->setIdentity($item->document_number)
                ->setPhone($item->phones[0]->number)
                ->setPhoneLocal($item->phones[0]->ddd)
                ->setAddressStreet($item->addresses[0]->street)
                ->setAddressNumber($item->addresses[0]->street_number)
                ->setAddressNeighborhood($item->addresses[0]->neighborhood)
                ->setAddressZipCode($item->addresses[0]->zipcode)
                ->setAddressCity($item->addresses[0]->city)
                ->setAddressState($item->addresses[0]->state)
                ->setAddressCountry($item->addresses[0]->country);

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
                'country' => 'BR',
                'address' => [
                    'street' => $customer->getAddressStreet(),
                    'street_number' => $customer->getAddressNumber(),
                    'neighborhood' => $customer->getAddressNeighborhood(),
                    'zipcode' => $customer->getAddressZipCode(),
                    'city' => $customer->getAddressCity(),
                    'state' => $customer->getAddressState(),
                    'country' => $customer->getAddressCountry()
                ],
                'email' => $customer->getEmail(),
                'document_type' => 'cpf',
                'document_number' => $customer->getIdentity(),
                'phone' => ['ddd' => $customer->getPhoneLocal(), 'number' => $customer->getPhone()]
            ],
            'metadata' => [
                'references' => $charge->getMeta(), 
                'references_index' => $this->getMetaIndex($charge->getMeta())
            ]
        ]);

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

        $key = 'ak_test_nFM7nDMKJA0TDNVGCaQNgnNzFG4GU0';

        $session = curl_init('https://api.pagar.me/1/transactions/' . $token);
    
        $post = json_encode([
          'api_key' => $key
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

                return (new \PHPBook\Payment\Charge)
                    ->setToken($item->id)
                    ->setPriceCents($item->amount)
                    ->setMeta($item->metadata->references)
                    ->setStatus($this->getChargeStatus($item->status));

            };

        } else {

            throw new \Exception($response);
            
        };
        
        return null;

    }

    public function getChargesByMeta(String $meta): Array { # Array of \PHPBook\Payment\Charge

        $session = curl_init('https://api.pagar.me/1/search');

        $post = json_encode([
            'api_key' => $this->getKey(),
            'type' => 'transaction',
            'query' => [
                    'query' => [
                        'query_string' => [
                            'query' => $this->getMetaIndex($meta),
                            'fields' => ['references_index'],
                            "default_operator" => "AND",
                        ],
                    ]
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
        
        $charges = [];

        if ($httpcode == '200') {
            
            $items = json_decode($response);

            foreach($items->hits->hits as $item) {
                
                $charges[] = (new \PHPBook\Payment\Charge)
                    ->setToken($item->_source->id)
                    ->setPriceCents($item->_source->amount)
                    ->setMeta($item->_source->metadata->references)
                    ->setStatus($this->getChargeStatus($item->_source->status));
                    
            };

        } else {

            throw new \Exception($response);
            
        };
        
        return $charges;

    }
    
}