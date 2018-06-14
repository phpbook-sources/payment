<?php namespace PHPBook\Payment\Configuration;

abstract class Payment {
    
    private static $gateway = [];

    private static $default;

    public static function setGateway(String $alias, Gateway $gateway) {
        Static::$gateway[$alias] = $gateway;
    }

    public static function getGateway(?String $alias): ?Gateway {
        return ($alias and array_key_exists($alias, Static::$gateway)) ? Static::$gateway[$alias] : (Static::$default ? Static::$gateway[Static::$default] : Null);
    }

    public static function getGateways(): Array {
        return Static::$gateway;
    }

    public static function setDefault(String $default) {
    	Static::$default = $default;
    }

    public static function getDefault(): ?String {
    	return Static::$default;
    }

}
