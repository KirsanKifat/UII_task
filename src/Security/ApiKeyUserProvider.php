<?php


namespace App\Security;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{

    private $em;

    public function __construct( EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getUsernameForApiKey($apiKey)
    {
//         Look up the username based on the token in the database, via
//         an API call, or do something entirely different

        $user = $this->em->getRepository(Users::class)->findOneBy(['api_key' => $apiKey]);
        if($user){
            $username = $user->getName();
        } else {
            throw new \Exception('Fail secret key');
        }

        return $username;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository(Users::class)->findOneBy(['name' => $username]);
        $role = $user->getAccessRights();
        return new User(
            $username,
            null,
            // the roles for the user - you may choose to determine
            // these dynamically somehow based on the user
            array($role)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}