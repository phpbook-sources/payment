<?php namespace PHPBook\Payment;

class Charge {
    
    public static $STATUS_COMPLETE = 'STATUS_COMPLETE';

    public static $STATUS_WAITING = 'STATUS_WAITING';

    public static $STATUS_DENY = 'STATUS_DENY';

    public static $STATUS_REFUNDED = 'STATUS_REFUNDED';

    public static $STATUS_IDLE = 'STATUS_IDLE';

    private $token;

    private $priceCents;

    private $meta;

    private $status;

    private $shippingAddressStreet;

    private $shippingAddressNumber;

    private $shippingAddressNeighborhood;

    private $shippingAddressZipCode;

    private $shippingAddressCity;

    private $shippingAddressState;

    private $shippingAddressCountry;

    function __construct() {
        $this->setStatus(Static::$STATUS_IDLE);
    }

    public function getToken(): ?String {
        return $this->token;
    }

    public function setToken(?String $token): Charge {
        $this->token = $token;
        return $this;
    }

    public function getPriceCents(): ?Float {
        return $this->priceCents;
    }

    public function setPriceCents(Float $priceCents): Charge {
        $this->priceCents = $priceCents;
        return $this;
    }

    public function getMeta(): ?String {
        return $this->meta;
    }

    public function setMeta(String $meta): Charge {
        $this->meta = $meta;
        return $this;
    }

    public function getStatus(): ?String {
        return $this->status;
    }

    public function setStatus(String $status): Charge {
        $this->status = $status;
        return $this;
    }

    public function getShippingAddressStreet(): ?String {
        return $this->shippingAddressStreet;
    }

    public function setShippingAddressStreet(String $shippingAddressStreet): Charge {
        $this->shippingAddressStreet = $shippingAddressStreet;
        return $this;
    }

    public function getShippingAddressNumber(): ?String {
        return $this->shippingAddressNumber;
    }

    public function setShippingAddressNumber(String $shippingAddressNumber): Charge {
        $this->shippingAddressNumber = $shippingAddressNumber;
        return $this;
    }

    public function getShippingAddressNeighborhood(): ?String {
        return $this->shippingAddressNeighborhood;
    }

    public function setShippingAddressNeighborhood(String $shippingAddressNeighborhood): Charge {
        $this->shippingAddressNeighborhood = $shippingAddressNeighborhood;
        return $this;
    }

    public function getShippingAddressZipCode(): ?String {
        return $this->shippingAddressZipCode;
    }

    public function setShippingAddressZipCode(String $shippingAddressZipCode): Charge {
        $this->shippingAddressZipCode = $shippingAddressZipCode;
        return $this;
    }

    public function getShippingAddressCity(): ?String {
        return $this->shippingAddressCity;
    }

    public function setShippingAddressCity(String $shippingAddressCity): Charge {
        $this->shippingAddressCity = $shippingAddressCity;
        return $this;
    }

    public function getShippingAddressState(): ?String {
        return $this->shippingAddressState;
    }

    public function setShippingAddressState(String $shippingAddressState): Charge {
        $this->shippingAddressState = $shippingAddressState;
        return $this;
    }

    public function getShippingAddressCountry(): ?String {
        return $this->shippingAddressCountry;
    }

    public function setShippingAddressCountry(String $shippingAddressCountry): Charge {
        $this->shippingAddressCountry = $shippingAddressCountry;
        return $this;
    }
    
    
}
