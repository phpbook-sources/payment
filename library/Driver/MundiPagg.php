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
  
        try {

            $client = new \MundiAPILib\MundiAPIClient($this->getKey());

            $customers = $client->getCustomers();

            $modelAddress = new \MundiAPILib\Models\CreateAddressRequest();
            $modelAddress->street = $customer->getAddressStreet();
            $modelAddress->number = $customer->getAddressNumber();
            $modelAddress->zipCode = $customer->getAddressZipCode();
            $modelAddress->neighborhood = $customer->getAddressNeighborhood();
            $modelAddress->city = $customer->getAddressCity();
            $modelAddress->state = $customer->getAddressState();
            $modelAddress->country = 'BR';

            $modelPhone = new \MundiAPILib\Models\CreatePhoneRequest(55, $customer->getPhone(), $customer->getPhoneLocal());
            $modelPhone->countryCode = '55';
            $modelPhone->number = $customer->getPhone();
            $modelPhone->areaCode = $customer->getPhoneLocal();

            $modelPhones = new \MundiAPILib\Models\CreatePhonesRequest();
            $modelPhones->homePhone = $modelPhone;

            $modelCustomer = new \MundiAPILib\Models\CreateCustomerRequest();
            $modelCustomer->name = $customer->getName();
            $modelCustomer->email = $customer->getEmail();
            $modelCustomer->document = $customer->getIdentity();
            $modelCustomer->type = 'individual';
            $modelCustomer->address = $modelAddress;
            $modelCustomer->metadata = ['country' => $customer->getAddressCountry()];
            $modelCustomer->phones = $modelPhones;

            $result = $customers->createCustomer($modelCustomer);
            
            if ($result->id) {

                $customer->setToken($result->id);
    
            };

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }

    public function getCustomer(String $token): ?\PHPBook\Payment\Customer {

        try {
                
            $client = new \MundiAPILib\MundiAPIClient($this->getKey());

            $customers = $client->getCustomers();

            $result = $customers->getCustomer($token);

            if ($result->id) {

                return (new \PHPBook\Payment\Customer)
                    ->setToken($result->id)
                    ->setName($result->name)
                    ->setEmail($result->email)
                    ->setIdentity($result->document)
                    ->setPhone($result->phones->homePhone->number)
                    ->setPhoneLocal($result->phones->homePhone->areaCode)
                    ->setAddressStreet($result->address->street)
                    ->setAddressNumber($result->address->number)
                    ->setAddressNeighborhood($result->address->neighborhood)
                    ->setAddressZipCode($result->address->zipCode)
                    ->setAddressCity($result->address->city)
                    ->setAddressState($result->address->state)
                    ->setAddressCountry($result->metadata['country']);

            };

            return null;

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }

    public function createCard(String $customerToken, Array $card): ?String {

        try {
                
            $client = new \MundiAPILib\MundiAPIClient($this->getKey());
            
            $customers = $client->getCustomers();

            list($cardNumber, $cardCvv, $cardName, $cardMonth, $cardYear) = $card;

            $modelCard = new \MundiAPILib\Models\CreateCardRequest();
            $modelCard->number = $cardNumber;
            $modelCard->holderName = $cardName;
            $modelCard->expMonth = $cardMonth;
            $modelCard->expYear = $cardYear;

            $result = $customers->createCard($customerToken, $modelCard);

            if ($result->id) {

                return $result->id;

            };

            return null;

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }
    
    public function createCharge(\PHPBook\Payment\Customer $customer, String $cardToken, \PHPBook\Payment\Charge $charge) {

        try {
                
            $client = new \MundiAPILib\MundiAPIClient($this->getKey());

            $charges = $client->getCharges();

            $customers = $client->getCustomers();

            $modelAddress = new \MundiAPILib\Models\CreateAddressRequest();
            $modelAddress->street = $customer->getAddressStreet();
            $modelAddress->number = $customer->getAddressNumber();
            $modelAddress->zipCode = $customer->getAddressZipCode();
            $modelAddress->neighborhood = $customer->getAddressNeighborhood();
            $modelAddress->city = $customer->getAddressCity();
            $modelAddress->state = $customer->getAddressState();
            $modelAddress->country = 'BR';

            $modelPhone = new \MundiAPILib\Models\CreatePhoneRequest(55, $customer->getPhone(), $customer->getPhoneLocal());
            $modelPhone->countryCode = '55';
            $modelPhone->number = $customer->getPhone();
            $modelPhone->areaCode = $customer->getPhoneLocal();

            $modelPhones = new \MundiAPILib\Models\CreatePhonesRequest();
            $modelPhones->homePhone = $modelPhone;

            $modelCustomer = new \MundiAPILib\Models\UpdateCustomerRequest();
            $modelCustomer->name = $customer->getName();
            $modelCustomer->email = $customer->getEmail();
            $modelCustomer->document = $customer->getIdentity();
            $modelCustomer->type = 'individual';
            $modelCustomer->address = $modelAddress;
            $modelCustomer->metadata = ['country' => $customer->getAddressCountry()];
            $modelCustomer->phones = $modelPhones;
            
            $customers->updateCustomer($customer->getToken(), $modelCustomer);

            $modelCard = new \MundiAPILib\Models\CreateCreditCardPaymentRequest();
            $modelCard->cardId = $cardToken;

            $modelPayment = new \MundiAPILib\Models\CreatePaymentRequest();
            $modelPayment->paymentMethod = 'credit_card';
            $modelPayment->creditCard = $modelCard;
            $modelPayment->amount = $charge->getPriceCents();
            $modelPayment->customerId = $customer->getToken();

            $modelCharge = new \MundiAPILib\Models\CreateChargeRequest();
            $modelCharge->code = $charge->getMeta();
            $modelCharge->amount = $charge->getPriceCents();
            $modelCharge->customerId = $customer->getToken();
            $modelCharge->payment = $modelPayment;

            $result = $charges->createCharge($modelCharge);

            if ($result->id) {

                $charge->setToken($result->id);

                $charge->setStatus($this->getChargeStatus($result->status));

            };

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }

    public function refundCharge(\PHPBook\Payment\Charge $charge) {

        try {

            $client = new \MundiAPILib\MundiAPIClient($this->getKey());

            $charges = $client->getCharges();

            $charges->cancelCharge($charge->getToken());

            $result = $charges->getCharge($charge->getToken());
            
            if ($result->id) {

                $charge->setStatus($this->getChargeStatus($result->status));

            };

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }

    public function getCharge(String $token): ?\PHPBook\Payment\Charge {

        try {

            $client = new \MundiAPILib\MundiAPIClient($this->getKey());

            $charges = $client->getCharges();

            $result = $charges->getCharge($token);
            
            if ($result->id) {

                return (new \PHPBook\Payment\Charge)
                    ->setToken($result->id)
                    ->setPriceCents($result->amount)
                    ->setMeta($result->code)
                    ->setStatus($this->getChargeStatus($result->status));

            };

            return null;

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }

    public function getChargesByMeta(String $meta): Array { # Array of \PHPBook\Payment\Charge

        try {
                
            $client = new \MundiAPILib\MundiAPIClient($this->getKey());

            $charges = $client->getCharges();

            $page = 1;

            $items = [];

            do {

                $results = $charges->getCharges($page, 10, $meta);

                foreach($results->data as $result) {

                    $items[] = (new \PHPBook\Payment\Charge)
                        ->setToken($result->id)
                        ->setPriceCents($result->amount)
                        ->setMeta($result->code)
                        ->setStatus($this->getChargeStatus($result->status));
        
                };

                $page++;

            } while($results->paging->next);      

            return $items;

        } catch(\MundiAPILib\Exceptions\ErrorException $e)  {

            throw new \Exception($e->getResponseBody());

        } catch(\Exception $e ) {
            
            throw new \Exception($e->getMessage());

        };

    }
    
}