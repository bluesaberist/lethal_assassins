<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface
{
  private $username;
  private $password;
  private $plainPassword = null;

  public function __construct($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
  }

  public function getRoles()
  {
    return ["ROLE_ADMIN"];
  }

  public function getPassword()
  {
    return $this->password;
  }

  public function getSalt()
  {
    return null;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function eraseCredentials()
  {
  }

  public function isEqualTo(UserInterface $user)
  {
    if (!$user instanceof User) {
      return false;
    }

    if ($this->password !== $user->getPassword()) {
      return false;
    }

    if ($this->username !== $user->getUsername()) {
      return false;
    }

    return true;
  }
}
