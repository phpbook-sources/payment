<?php namespace PHPBook\Payment;

class Customer {

    private $token;

    private $name;

    private $email;

    private $identity;

    private $phone;

    private $phoneLocal;

    private $phoneCountry;

    public function getToken(): ?String {
        return $this->token;
    }

    public function setToken(?String $token): Customer {
        $this->token = $token;
        return $this;
    }

    public function getName(): ?String {
        return $this->name;
    }

    public function setName(String $name): Customer {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?String {
        return $this->email;
    }

    public function setEmail(String $email): Customer {
        $this->email = $email;
        return $this;
    }

    public function getIdentity(): ?String {
        return $this->identity;
    }

    public function setIdentity(String $identity): Customer {
        $this->identity = $identity;
        return $this;
    }

    public function getPhone(): ?String {
        return $this->phone;
    }

    public function setPhone(String $phone): Customer {
        $this->phone = $phone;
        return $this;
    }

    public function getPhoneLocal(): ?String {
        return $this->phoneLocal;
    }

    public function setPhoneLocal(String $phoneLocal): Customer {
        $this->phoneLocal = $phoneLocal;
        return $this;
    }

    public function getPhoneCountry(): ?String {
        return $this->phoneCountry;
    }

    public function setPhoneCountry(String $phoneCountry): Customer {
        $this->phoneCountry = $phoneCountry;
        return $this;
    }

}
