<?php namespace PHPBook\Payment\Driver;

class MundiPagg extends Adapter {
    	
   	private $key;

    public function getKey(): String {
    	return $this->key;
    }

    public function setKey(String $key): MundiPagg {
    	$this->key = $key;
    	return $this;
    }

    public function getChargeStatus(String $gatewayStatus): String {

         switch($gatewayStatus) {

            case 'paid':
            case 'overpaid':
                   return \PHPBook\Payment\Charge::$STATUS_COMPLETE;
                break;

            case 'pending':
            case 'processing':
                    return \PHPBook\Payment\Charge::$STATUS_WAITING;
                break;

            case 'failed':
            case 'underpaid':
                    return \PHPBook\Payment\Charge::$STATUS_DENY;
                break;

            case 'canceled':
                   return \PHPBook\Payment\Charge::$STATUS_REFUNDED;
                break;

            default:
                    return \PHPBook\Payment\Charge::$STATUS_IDLE;
                break;

        };

    }

    public function createCustomer(\PHPBook\Payment\Customer $customer) {

        $post = json_encode([
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'code' => null,
            'document' => $customer->getIdentity(),
            'type' => 'individual',
            'gender' => null,
            'address' => [
                'line_1' => $customer->getAddressStreet(),
                'line_2' => $customer->getAddressNeighborhood(),
                'zip_code' => $customer->getAddressZipCode(),
                'city' => $customer->getAddressCity(),
                'state' => $customer->getAddressState(),
                'country' => 'BR',
            ],
            'birthdate' => null,
            'phones' => [
                'home_phone' => [
                    'country_code' => '55',
                    'area_code' => $customer->getPhoneLocal(),
                    'number' => $customer->getPhone()
                ],
                'mobile_phone' => null
            ],
            'metadata' => [
                'address_country' => $customer->getAddressCountry(),
                'address_number' => $customer->getAddressNumber(),
            ],
        ]);
    
        $post = utf8_encode($post);
    
        $session = curl_init('https://api.mundipagg.com/core/v1/customers');
    
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
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

            $customer->setToken($item->id);

        } else {

            throw new \Exception($response);

        };

    }

    public function getCustomer(String $token): ?\PHPBook\Payment\Customer {

        $session = curl_init('https://api.mundipagg.com/core/v1/customers/' . $token);

        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($session);

        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);

        curl_close($session);
        
        if ($httpcode == '200') {

            $item = json_decode($response);
            
            if ($item->id) {
 
                return (new \PHPBook\Payment\Customer)
                    ->setToken($item->id)
                    ->setName($item->name)
                    ->setEmail($item->email)
                    ->setIdentity($item->document)
                    ->setPhone($item->phones->home_phone->number)
                    ->setPhoneLocal($item->phones->home_phone->area_code)
                    ->setAddressStreet($item->address->line_1)
                    ->setAddressNumber($item->metadata->address_number)
                    ->setAddressNeighborhood($item->address->line_2)
                    ->setAddressZipCode($item->address->zip_code)
                    ->setAddressCity($item->address->city)
                    ->setAddressState($item->address->state)
                    ->setAddressCountry($item->metadata->address_country);
                    
            };

        } else {

            throw new \Exception($response);

        };
        
        return null;

    }

    public function createCard(String $customerToken, Array $card): ?String {

        list($cardNumber, $cardCvv, $cardName, $cardMonth, $cardYear) = $card;

        $post = json_encode([
            'number' => $cardNumber,
            'holder_name' => $cardName,
            'exp_month' => $cardMonth,
            'exp_year' => $cardYear
        ]);

        $post = utf8_encode($post);
            
        $session = curl_init('https://api.mundipagg.com/core/v1/customers/'.$customerToken.'/cards');

        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
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
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'code' => null,
            'document' => $customer->getIdentity(),
            'type' => 'individual',
            'gender' => null,
            'address' => [
                'line_1' => $customer->getAddressStreet(),
                'line_2' => $customer->getAddressNeighborhood(),
                'zip_code' => $customer->getAddressZipCode(),
                'city' => $customer->getAddressCity(),
                'state' => $customer->getAddressState(),
                'country' => 'BR',
            ],
            'birthdate' => null,
            'phones' => [
                'home_phone' => [
                    'country_code' => '55',
                    'area_code' => $customer->getPhoneLocal(),
                    'number' => $customer->getPhone()
                ],
                'mobile_phone' => null
            ],
            'metadata' => [
                'address_country' => $customer->getAddressCountry(),
                'address_number' => $customer->getAddressNumber(),
            ],
        ]);
    
        $post = utf8_encode($post);
    
        $session = curl_init('https://api.mundipagg.com/core/v1/customers/' . $customer->getToken());
    
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
        curl_setopt($session, CURLOPT_POSTFIELDS, $post);
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($session);
    
        $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
        
        curl_close($session);

        if ($httpcode == '200') {

            $post = json_encode([
                'code' => $charge->getMeta(),
                'amount' => $charge->getPriceCents(),
                'customer_id' => $customer->getToken(),
                'payment' => [
                    'payment_method' => 'credit_card',
                    'credit_card' => [
                       'card_id' => $cardToken
                    ],
                    'amount' => $charge->getPriceCents(),
                    'customer_id' => $customer->getToken()
                ]
            ]);
    
            $post = utf8_encode($post);
    
            $session = curl_init('https://api.mundipagg.com/core/v1/charges');
    
            curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($session, CURLOPT_HTTPHEADER, [
                'accept:application/json',
                'content-type:application/json; charset=utf-8'
            ]);
            curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
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
            
        } else {

            throw new \Exception($response);

        };

    }

    public function refundCharge(\PHPBook\Payment\Charge $charge) {

        $post = json_encode([
            'amount' => null
        ]);

        $post = utf8_encode($post);
            
        $session = curl_init('https://api.mundipagg.com/core/v1/charges/' . $charge->getToken());

        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
        curl_setopt($session, CURLOPT_CUSTOMREQUEST, 'DELETE');
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

        $session = curl_init('https://api.mundipagg.com/core/v1/charges/' . $token);

        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_HTTPHEADER, [
            'accept:application/json',
            'content-type:application/json; charset=utf-8'
        ]);
        curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
        curl_setopt($session, CURLOPT_HEADER, false);
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
                ->setMeta($item->code)
                ->setStatus($this->getChargeStatus($item->status));

            };

        } else {

            throw new \Exception($response);

        };

        return null;

    }

    public function getChargesByMeta(String $meta): Array { # Array of \PHPBook\Payment\Charge

        $page = 1;

        $items = [];

        do {

            $session = curl_init('https://api.mundipagg.com/core/v1/charges?code='.$meta.'&page='.$page.'&size=10');

            curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($session, CURLOPT_HTTPHEADER, [
                'accept:application/json',
                'content-type:application/json; charset=utf-8'
            ]);
            curl_setopt($session, CURLOPT_USERPWD, $this->getKey() . ':');
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    
            $response = curl_exec($session);
    
            $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
    
            curl_close($session);

            $results = false;

            if ($httpcode == '200') {
                
                $results = json_decode($response);

                foreach($results->data as $result) {

                    $items[] = (new \PHPBook\Payment\Charge)
                        ->setToken($result->id)
                        ->setPriceCents($result->amount)
                        ->setMeta($result->code)
                        ->setStatus($this->getChargeStatus($result->status));

                 };

            } else {

                throw new \Exception($response);
    
            };

            $page++;

        } while(($results) and (isset($results->paging->next)));
      
        return $items;

    }
    
}