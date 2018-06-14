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
    
}
