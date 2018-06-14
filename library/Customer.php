<?php namespace PHPBook\Payment;

class Customer {

    private $token;

    private $name;

    private $email;

    private $identity;

    private $phone;

    private $phoneLocal;

    private $addressStreet;

    private $addressNumber;

    private $addressNeighborhood;

    private $addressZipCode;

    private $addressCity;

    private $addressState;

    private $addressCountry;

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

    public function getAddressStreet(): ?String {
        return $this->addressStreet;
    }

    public function setAddressStreet(String $addressStreet): Customer {
        $this->addressStreet = $addressStreet;
        return $this;
    }

    public function getAddressNumber(): ?String {
        return $this->addressNumber;
    }

    public function setAddressNumber(String $addressNumber): Customer {
        $this->addressNumber = $addressNumber;
        return $this;
    }

    public function getAddressNeighborhood(): ?String {
        return $this->addressNeighborhood;
    }

    public function setAddressNeighborhood(String $addressNeighborhood): Customer {
        $this->addressNeighborhood = $addressNeighborhood;
        return $this;
    }

    public function getAddressZipCode(): ?String {
        return $this->addressZipCode;
    }

    public function setAddressZipCode(String $addressZipCode): Customer {
        $this->addressZipCode = $addressZipCode;
        return $this;
    }

    public function getAddressCity(): ?String {
        return $this->addressCity;
    }

    public function setAddressCity(String $addressCity): Customer {
        $this->addressCity = $addressCity;
        return $this;
    }

    public function getAddressState(): ?String {
        return $this->addressState;
    }

    public function setAddressState(String $addressState): Customer {
        $this->addressState = $addressState;
        return $this;
    }

    public function getAddressCountry(): ?String {
        return $this->addressCountry;
    }

    public function setAddressCountry(String $addressCountry): Customer {
        $this->addressCountry = $addressCountry;
        return $this;
    }
    
}
