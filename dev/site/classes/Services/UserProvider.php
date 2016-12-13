<?php
/**
 * Created by PhpStorm.
 * User: andriy
 * Date: 13.12.16
 * Time: 14:43
 *
 * see sample http://silex.sensiolabs.org/doc/master/providers/security.html
 */

namespace App\Services;


use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Models\User;


class UserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        if (false) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        return new User($username, explode(',', 'ROLE_MANAGER'));
    }

    public function refreshUser(UserInterface $user)
    {
        // TODO: Implement refreshUser() method.
        return $user;
    }

    public function supportsClass($class)
    {
        return $class = User::class;
    }

}