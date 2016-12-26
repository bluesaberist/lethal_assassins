<?php
// src/AppBundle/Security/UserProvider.php
namespace AppBundle\Security;

use AppBundle\Security\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface
{
  protected $dbh;

  public function __construct(Connection $dbh)
  {
    $this->dbh = $dbh;
  }

  public function loadUserByUsername($username)
  {
    $getUserQuery = <<<EOT
SELECT * FROM user WHERE email = :username
EOT;
    $result = $this->dbh->fetchAll($getUserQuery, ["username" => $username]);

    if(count($result) !== 1) {
      throw new UsernameNotFoundException(
        sprintf('Username "%s" does not exist.', $username)
      );
    }

    $dbUser = $result[0];

    return new User($dbUser["email"], $dbUser["password"]);
  }

  public function refreshUser(UserInterface $user)
  {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(
        sprintf('Instances of "%s" are not supported.', get_class($user))
      );
    }

    return $this->loadUserByUsername($user->getUsername());
  }

  public function supportsClass($class)
  {
    return User::class === $class;
  }
}
