    
+ [About Payment](#about-payment)
+ [Composer Install](#composer-install)
+ [Declare Configurations](#declare-configurations)
+ [Transactions](#transactions)

### About Payment

- A lightweight Payment Gateway PHP library available for PagarMe and MundiPagg.

### Composer Install

	composer require phpbook/payment

### Declare Configurations

```php

/********************************************
 * 
 *  Declare Configurations
 * 
 * ******************************************/

//Driver gateway PagarMe

\PHPBook\Payment\Configuration\Payment::setGateway('main',
	(new \PHPBook\Payment\Configuration\Gateway)
		->setName('Main')
		->setExceptionCatcher(function(String $message) {
			//the PHPBook Payment does not throw exceptions, but you can take it here
			//you can store $message in database or something else
		})
		->setDriver((new \PHPBook\Payment\Driver\PagarMe)->setKey('key'))
);

//Driver gateway MundiPagg

\PHPBook\Payment\Configuration\Payment::setGateway('backups',
	(new \PHPBook\Payment\Configuration\Gateway)
		->setName('Backups')
		->setExceptionCatcher(function(String $message) {
			//the PHPBook Payment does not throw exceptions, but you can take it here
			//you can store $message in database or something else
		})
		->setDriver((new \PHPBook\Payment\Driver\MundiPagg)->setKey('key'))
);


//Set default gateway by gateway code

\PHPBook\Payment\Configuration\Payment::setDefault('main');

//Getting gateways

$gateways = \PHPBook\Payment\Configuration\Payment::getGateways();

foreach($gateways as $code => $gateway) {

	$gateway->getName(); 

	$gateway->getDriver();

};

?>
```

### Transactions

```php
		

/*************************************************
 * 
 *  Create Customer
 *  You cannot update customer here, only create
 *
 * ***********************************************/

	$customer = (new \PHPBook\Payment\Customer)
		->setToken(null) //gateway defines when create
		->setName('Jhon Doe')
		->setEmail('jhon@email.com')
		->setIdentity('16815587002')
		->setPhone('999999999')
		->setPhoneLocal('47')
		->setAddressStreet('Praça Gov. Irineu Bornhausen')
		->setAddressNumber('100')
		->setAddressNeighborhood('Centro')
		->setAddressZipCode('88310-000')
		->setAddressCity('Itajaí')
		->setAddressState('SC')
		->setAddressCountry('Brasil');

	//getting customer attributes
	$customer->getToken();
	$customer->getName();
	$customer->getEmail();
	$customer->getIdentity();
	$customer->getPhone();
	$customer->getPhoneLocal();
	$customer->getAddressStreet();
	$customer->getAddressNumber();
	$customer->getAddressNeighborhood();
	$customer->getAddressZipCode();
	$customer->getAddressCity();
	$customer->getAddressState();
	$customer->getAddressCountry();

	(new \PHPBook\Payment\Transaction\Customer\Create)
		->setCustomer($customer)
		->create();

	//filled when customer is successfully created
	if ($customer->getToken()) {
		//$customer
	};

/*************************************************
 * 
 *  Get Customer By Token
 *
 * ***********************************************/

$customerToken = '0001';

$customer = (new \PHPBook\Payment\Transaction\Customer\Get)
	->setToken($customerToken)
	->get();

//filled with customer
if ($customer) {
	//$customer
};

/*************************************************
 * 
 *  Create Card
 *  You cannot update card here, only create
 *
 * ***********************************************/
	
	$number = '000000000000';
	$cvv = '123';
	$name = 'JHON DOE';
	$month = '01';
	$year = '20';

	$cardToken = (new \PHPBook\Payment\Transaction\Card\Create)
		->setCustomerToken($customerToken)
		->setCard($number, $cvv, $name, $month, $year)
		->create();

	//filled when card is successfully created
	if ($cardToken) {
		//$cardToken
	};

/*************************************************
 * 
 *  Create Charge
 *  You cannot update card here, only create
 *
 * ***********************************************/

	/********************************************************/
	/* Create Charge with Created Customer and Created Card */
	/********************************************************/

	$customerToken = '0001';

	$cardToken = '0001';

	$customer = (new \PHPBook\Payment\Transaction\Customer\Get)
		->setToken($customerToken)
		->get();

	//if you need you can update customer informations in the gateway
	$customer->setName('name');

	$charge = (new \PHPBook\Payment\Charge)
		->setToken(null) //gateway defines when create
		->setMeta('billing-10')
		->setPriceCents(100);

	(new \PHPBook\Payment\Transaction\Charge\Create)
		->setCustomer($customer)
		->setCardToken($cardToken)
		->setCharge($charge)
		->create();

	//filled when charge is successfully created
	if ($charge->getToken()) {

		$charge->getStatus(); //filled with new status 
			
	};

	/****************************************************/
	/* Create Charge with Created Customer and New Card */
	/****************************************************/

	$customerToken = '0001';

	$cardNumber = '000000000000';
	$cardCvv = '123';
	$cardName = 'JHON DOE';
	$cardMonth = '01';
	$cardYear = '20';

	$customer = (new \PHPBook\Payment\Transaction\Customer\Get)
		->setToken($customerToken)
		->get();

	$cardToken = (new \PHPBook\Payment\Transaction\Card\Create)
		->setCustomerToken($customerToken)
		->setCard($cardNumber, $cardCvv, $cardName, $cardMonth, $cardYear)
		->create();

	//filled when card is successfully created
	if ($cardToken) {

		//if you need you can update customer informations in the gateway
		$customer->setName('name');

		$charge = (new \PHPBook\Payment\Charge)
			->setToken(null) //gateway defines when create
			->setMeta('billing-10')
			->setPriceCents(100);

		(new \PHPBook\Payment\Transaction\Charge\Create)
			->setCustomer($customer)
			->setCardToken($cardToken)
			->setCharge($charge)
			->create();

		//filled when charge is successfully created
		if ($charge->getToken()) {

			$charge->getStatus(); //filled with new status 
			
		};

	};

	/************************************************/
	/* Create Charge with New Customer and New Card */
	/************************************************/

	$name = 'Jhon Doe';
	$email = 'jhon@email.com';
	$identity = '01684848421';
	$phone = '999999999999';
	$phoneLocal = '47';
	$addressStreet = 'Praça Gov. Irineu Bornhausen';
	$addressNumber = '100';
	$addressNeighborhood = 'Centro';
	$addressZipCode = '88310000';
	$addressCity = 'Itajaí';
	$addressState = 'SC';
	$addressCountry = 'Brasil';

	$cardNumber = '000000000000';
	$cardCvv = '123';
	$cardName = 'JHON DOE';
	$cardMonth = '01';
	$cardYear = '20';
	
	$customer = (new \PHPBook\Payment\Customer)
		->setToken(null) //gateway defines when create
		->setName($name)
		->setEmail($email)
		->setIdentity($identity)
		->setPhone($phone)
		->setPhoneLocal($phoneLocal)
		->setAddressStreet($addressStreet)
		->setAddressNumber($addressNumber)
		->setAddressNeighborhood($addressNeighborhood)
		->setAddressZipCode($addressZipCode)
		->setAddressCity($addressCity)
		->setAddressState($addressState)
		->setAddressCountry($addressCountry);

	(new \PHPBook\Payment\Transaction\Customer\Create)
		->setCustomer($customer)
		->create();

	//filled when customer is successfully created
	if ($customer->getToken()) {

		$cardToken = (new \PHPBook\Payment\Transaction\Card\Create)
			->setCustomerToken($customer->getToken())
			->setCard($cardNumber, $cardCvv, $cardName, $cardMonth, $cardYear)
			->create();

		//filled when card is successfully created
		if ($cardToken) {

			$charge = (new \PHPBook\Payment\Charge)
				->setToken(null) //gateway defines when create
				->setMeta('billing-10')
				->setPriceCents(100);
						
			(new \PHPBook\Payment\Transaction\Charge\Create)
				->setCustomer($customer)
				->setCardToken($cardToken)
				->setCharge($charge)
				->create();

			//filled when charge is successfully created
			if ($charge->getToken()) {

				$charge->getStatus(); //filled with new status 
				
			};


		};
			
	};

/*************************************************
 * 
 *  Working With Charge Attributes and Status
 *
 * ***********************************************/

	$charge->getToken();
	
	$charge->getPriceCents();

	$charge->getMeta();

	switch($charge->getStatus()) {

		case \PHPBook\Payment\Charge::$STATUS_COMPLETE:
				//complete
			break;

		case \PHPBook\Payment\Charge::$STATUS_WAITING:
				//waiting
			break;

		case \PHPBook\Payment\Charge::$STATUS_DENY:
				//deny
			break;

		case \PHPBook\Payment\Charge::$STATUS_REFUNDED:
				//refunded
			break;

		case \PHPBook\Payment\Charge::$STATUS_IDLE:
				//initial status
			break;

	};

/*************************************************
 * 
 *  Get Charge By Token
 *
 * ***********************************************/
	
	$chargeToken = '00001';

	$charge = (new \PHPBook\Payment\Transaction\Charge\Get)
		->setToken($chargeToken)
		->get();

/*************************************************
 * 
 *  Get List of Charges By Meta
 *
 * ***********************************************/
	
	$meta = 'billing-10';

	$charges = (new \PHPBook\Payment\Transaction\Charge\Meta\Get)
		->setMeta($meta)
		->get();

/*************************************************
 * 
 *  Refund Charge
 *
 * ***********************************************/

	$chargeToken = '00001';

	$charge = (new \PHPBook\Payment\Transaction\Charge\Get)
		->setToken($chargeToken)
		->get();

	(new \PHPBook\Payment\Transaction\Charge\Refund)
		->setCharge($charge)
		->refund();

	$charge->getStatus(); //filled with new status. You should expect the refunded status
	
```