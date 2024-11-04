<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

interface Seguridad_interface {
    
    /**
     * Obtener privilegios por controlador para uno o varios roles de un usuario en el contexto de una aplicación específica.
     *
     * @param int       $idUser         ID del usuario.
     * @param int       $idApp          ID de la aplicación.
     * @param int       $idRol          ID del rol del que se requieran obtener los permisos.
     * @param mixed     $idController   ID del controlador (opcional) puede ser int o array dependiendo los controladores sobre los
     *                                  que se requieran obtener los permisos, si es NULL, se obtendrán los permisos de todos los controladores.
     *
     * @return array Regresa un arreglo de permisos (cat_permiso), indexado por el idRol/idControlador/idPermiso. 
     */
    public function getPrivilege(int $idUser, int $idApp, int $idRol, $idController = NULL) : array;
    
    /*
     * Método para normalizar la llamada al método getPrivilege con base en los valores de la sesión actual de la solicitud.
     */
    public function getPrivilegeToCurrentRequest();
    
    /**
     * Método para agregar/editar un registro en la tabla app_rol_usuario.
     *
     * @param array     $reg            Arreglo con los campos del registro a insertar/editar.
     * @param int       $idReg          ID del registro (opcional), si no se proporciona, se trata de un registro nuevo; 
     *                                  en otro caso, de una actualización.
     *
     * @return array Regresa un arreglo con el resultado de la acción solicitada para el registro. 
     */
    public function saveAppRol(array $reg, int $idReg = NULL) : array;
    
    /**
     * Búsqueda de roles y/o aplicaciones de usuario(s) en la tabla app_rol_menu.
     * 
     * NOTA: Este método debe usar el método searchByModel que se hereda de la clase class_service.php
     *
     * @param array     $filter         Arreglo con filtros de búsqueda.
     * @param array     $extras         Arreglo con parámetros para personalizar búsqueda.
     *
     * @return array Regresa un arreglo de registros (si existen en la búsqueda realizada). 
     */
//    public function searchAppRol($filter = [], $extras = []) : array;    
    
    /**
     * Método para agregar/editar un registro en la tabla permisoxrol.
     *
     * @param array     $reg            Arreglo con los campos del registro a insertar/editar.
     * @param int       $idReg          ID del registro (opcional), si no se proporciona, se trata de un registro nuevo; 
     *                                  en otro caso, de una actualización.
     *
     * @return array Regresa un arreglo con el resultado de la acción solicitada para el registro. 
     */
    public function savePrivilegeRol(array $reg, int $idReg = NULL) : array;
    
    /**
     * Método para agregar/editar un registro en la tabla token_sesion.
     *
     * @param array     $reg            Arreglo con los campos del registro a insertar/editar.
     * @param int       $idReg          ID del registro (opcional), si no se proporciona, se trata de un registro nuevo; 
     *                                  en otro caso, de una actualización.
     *
     * @return array Regresa un arreglo con el resultado de la acción solicitada para el registro. 
     */
    public function saveTokenSession(array $reg, int $idReg = NULL) : array;
    
    /**
     * Búsqueda de tokens de sesión en la tabla token_sesion.
     * 
     * NOTA: Este método debe usar el método searchByModel que se hereda de la clase class_service.php
     *
     * @param array     $filter         Arreglo con filtros de búsqueda.
     * @param array     $extras         Arreglo con parámetros para personalizar búsqueda.
     *
     * @return array Regresa un arreglo de registros (si existen en la búsqueda realizada). 
     */
//    public function searchTokenSession($filter = [], $extras = []) : array;
    
    /**
     * Método para agregar un registro en la tabla password_reset.
     *
     * Nota: Este método debe poner la bandera de activo = 0, para todos los registros pre existentes (en esta tabla) del usuario en turno.
     * 
     * @param array     $reg            Arreglo con los campos del registro a insertar.
     *
     * @return array Regresa un arreglo con el resultado de la acción solicitada para el registro. 
     */
    public function savePasswordReset(array $reg) : array;
    
    /**
     * Método para agregar/editar un registro en la tabla password_reset.
     *
     * @param array     $reg            Arreglo con los campos del registro a insertar/editar.
     * @param int       $idReg          ID del registro (opcional), si no se proporciona, se trata de un registro nuevo; 
     *                                  en otro caso, de una actualización.
     *
     * @return array Regresa un arreglo con el resultado de la acción solicitada para el registro. 
     */
    public function saveMenuRolUser(array $reg, int $idReg = NULL) : array;
    
}
