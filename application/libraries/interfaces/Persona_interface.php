<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

interface Persona_interface {
    
    public function saveContactMean($idPerson, $reg = [], $id = NULL, $varPostIndex = NULL, $method = NULL);
    
    public function getContactMean($idPerson, $type = [], $priority = []);
    
    public function deleteContactMean($idPerson, $idContact = NULL, $cond = NULL);
}