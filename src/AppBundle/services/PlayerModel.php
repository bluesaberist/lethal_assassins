<?php

namespace AppBundle\services;

use Doctrine\DBAL\Connection;

/**
 *
 */
class PlayerModel
{
  protected $dbh;

  public function __construct(Connection $dbh)
  {
    $this->dbh = $dbh;
  }

  public function insertPlayer($name, $rank, $joinDate, $active = 1, $discordId = null, $email = null, $password = null)
  {
    $insertQuery = <<<EOT
INSERT INTO `lethal_assassins`.`player`
(`name`, `rank`, `joindate`, `discordId`, `active`, `email`, `password`)
VALUES (:name, :rank, :joindate, :discordId, :active, :email, :password);
EOT;
    try {
      $this->insertRow($insertQuery, [
        "id" => $id,
        "name" => $name,
        "rank" => $rank,
        "joindate" => $joinDate,
        "discordId" => $discordId,
        "active" => $active,
        "email" => $email,
        "password" => $password,
      ]);
      return true;
    }
    catch(Exception $e) {
      dump($e);
      return false;
    }
  }

  public function editPlayer($id, $fields)
  {
    if(!is_numeric($id)) {
      dump("Editing player, bad ID given (non-numeric)");
      return false;
    }
    $availableFields = ["name", "rank", "joindate", "discordId", "active", "email", "password"];
    $editQuery = "UPDATE `lethal_assassins`.`player` SET ";
    $queryFieldItems = [];
    $paremeters = [];
    foreach ($availableFields as $fieldName) {
      if(isset($fields[$fieldName])) {
        if($fieldName === "joindate") {
          $date = \DateTime::createFromFormat('Y-m-d', $request->request->get("date"));
          if($date === false) {
            continue;
          }
          $queryFieldItems[] = "`$fieldName` = :$fieldName";
          $paremeters[$fieldName] = $date;
        }
        else {
          $queryFieldItems[] = "`$fieldName` = :$fieldName";
          $paremeters[$fieldName] = $fields[$fieldName];
        }
      }
    }
    if(count($queryFieldItems) < 1) {
      dump("Editing player $id, no new fields found.");
      return false;
    }

    $editQuery .= join(", ", $queryFieldItems) . " WHERE `id` = :id";

    try {
      $this->insertRow($editQuery, $parameters);
      return true;
    }
    catch(Exception $e) {
      dump($e);
      return false;
    }
  }

  public function insertRow($query, $parameters)
  {
    $this->dbh->beginTransaction();
    $statement = $this->dbh->executeQuery($query, $parameters);
    if($statement->rowCount() !== 1) {
      $this->dbh->rollback();
      throw new \Exception();
    }
    $this->dbh->commit();
    return true;
  }
}
