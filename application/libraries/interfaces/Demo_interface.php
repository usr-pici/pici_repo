<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User {

    public $name;
    public $email;
    public $password;

    public function __construct() {
        $this->name = 'Nestor';
        $this->email = 'ing.nestorricardo@gmail.com';
    }

    public function getNombre() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

}

interface Demo_interface {
    
    public function demoTest($str) : String;

    public function raizCuadrada(float $numero) : float;

    public function potencia(int $numero, int $potencia): int;

    public function generate() : User;

    public static function init(?string $url): String;


}