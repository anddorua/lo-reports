<?php
/**
 * Created by PhpStorm.
 * User: andriy
 * Date: 13.12.16
 * Time: 14:35
 */

namespace App\Models;


use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $userName;
    private $roles;

    /**
     * User constructor.
     * @param $userName string
     * @param $roles array
     */
    public function __construct($userName, $roles)
    {
        $this->userName = $userName;
        $this->roles = $roles;
    }


    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
        return '';
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        return null;
    }

    public function getUsername()
    {
        return $this->userName;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

}