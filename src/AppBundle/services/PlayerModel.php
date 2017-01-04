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

  public function getPlayers()
  {
    $selectQuery = <<<EOT
SELECT `id`, `name`, `rank`, `joindate`, `discordId`
  FROM `lethal_assassins`.`player`
  WHERE `active` = 1
  ORDER BY `rank` ASC, `joindate` ASC
EOT;
    try {
      $players = $this->dbh->query($selectQuery);
      return $players;
    }
    catch (\Exception $e) {
      dump($e);
      return null;
    }
  }

  public function getPlayersNotInGame($gameId)
  {
    $selectQuery = <<<EOT
SELECT
    player.`id`, player.`name`, player.`rank`, player.`joindate`, player.`discordId`
FROM
    `lethal_assassins`.`player` AS player
        LEFT JOIN
    `lethal_assassins`.`EslGamePlayers` AS gamePlayer ON player.id = gamePlayer.playerId
        AND gamePlayer.EslGameId = :eslGameId
WHERE
    player.`active` = 1
        AND gamePlayer.EslGameId IS null
ORDER BY player.`rank` ASC , player.`name` ASC
EOT;
    try {
      $players = $this->dbh->executeQuery($selectQuery, ["eslGameId" => $gameId])->fetchAll(\PDO::FETCH_ASSOC);
      return $players;
    }
    catch (\Exception $e) {
      dump($e);
      return null;
    }
  }

  public function getPlayersInGame($gameId)
  {
    $selectQuery = <<<EOT
SELECT
    player.`id`,
    player.`name`,
    player.`rank`,
    player.`joindate`,
    player.`discordId`,
    gamePlayer.`commitment`
FROM
    `lethal_assassins`.`player` AS player
        JOIN
    `lethal_assassins`.`EslGamePlayers` AS gamePlayer ON player.id = gamePlayer.playerId
        AND gamePlayer.EslGameId = :eslGameId
WHERE
    player.`active` = 1
ORDER BY player.`rank` ASC , player.`joindate` ASC
EOT;
    try {
      return $this->dbh->executeQuery($selectQuery, ["eslGameId" => $gameId])->fetchAll(\PDO::FETCH_ASSOC);
    }
    catch (\Exception $e) {
      dump($e);
      return null;
    }
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
