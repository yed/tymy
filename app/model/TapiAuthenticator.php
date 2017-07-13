<?php

namespace App\Model;

use Nette;

/**
 * Users management.
 */
class TapiAuthenticator implements Nette\Security\IAuthenticator {

    /** @var Supplier */
    private $supplier;
    
    public function __construct(Supplier $supplier) {
        $this->supplier = $supplier;
    }
    
    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        $credentials[1] = md5($credentials[1]); // first login recodes password to md5 hash
        $loginObj = $this->reAuthenticate($credentials);
        $arr = (array) $loginObj->result;
        $arr["hash"] = $credentials[1];
        $arr["tapi_config"] = $this->supplier->getTapi_config();
        return new Nette\Security\Identity($loginObj->result->data->id, $loginObj->result->data->roles, $arr );
    }
    
    public function reAuthenticate(array $credentials){
        list($username, $password) = $credentials;
        $login = new \Tymy\Login($this->supplier);
        try {
            $login
                    ->setUsername($username)
                    ->setPassword($password)
                    ->fetch();
        } catch (\Tymy\Exception\APIException $exc) {
            throw new Nette\Security\AuthenticationException('Login failed.', self::INVALID_CREDENTIAL);
        }

        return $login;
    }
    
    //TODO password lost
    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @param  string
     * @param  string
     * @return void
     * @throws \Nette\InvalidArgumentException
     */
    public function add($user, $login, $password, $email) {
        try {
            $user
                    ->setLogin($login)
                    ->setPassword($password)
                    ->setEmail($email)
                    ->create();
        } catch (\Tymy\Exception\APIException $exc) {
            throw new \Nette\InvalidArgumentException($exc->getMessage(), self::FAILURE);
        }
    }

}