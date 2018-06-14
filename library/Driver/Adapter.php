<?php namespace PHPBook\Payment\Driver;

abstract class Adapter {
    
    /* returns charge status according to gateway status */
    public abstract function getChargeStatus(String $gatewayStatus): String;
    
    /* updates $customer->setToken($token) */
    /* requires a $customer without token */
    public abstract function createCustomer(\PHPBook\Payment\Customer $customer);

    /* returns $customer when success or null */
    public abstract function getCustomer(String $token): ?\PHPBook\Payment\Customer;

    /* returns $cardToken when success or null */
    public abstract function createCard(String $customerToken, Array $card): ?String;
    
    /* updates $charge->setToken($token) and $charge->setStatus($status) */
    /* requires a created $customer with token but the $customer informations can be setted and updated inside the gateway */
    public abstract function createCharge(\PHPBook\Payment\Customer $customer, String $cardToken, \PHPBook\Payment\Charge $charge);

    /* updates $charge->setStatus($status) */
    /* requires a created $charge with token */
    public abstract function refundCharge(\PHPBook\Payment\Charge $charge);
    
    /* returns $charge when success or null */
    public abstract function getCharge(String $token): ?\PHPBook\Payment\Charge;

    /* returns array of $charges when success or empty array */
    public abstract function getChargesByMeta(String $meta): Array; # Array of \PHPBook\Payment\Charge

}