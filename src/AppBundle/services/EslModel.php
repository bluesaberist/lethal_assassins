<?php

namespace AppBundle\services;

use Doctrine\DBAL\Connection;

/**
 *
 */
class EslModel
{
  protected $dbh;

  public function __construct(Connection $dbh)
  {
    $this->dbh = $dbh;
  }

  public function getGames($limit = 10)
  {
    $listGamesQuery = <<<EOT
SELECT
    `EslGame`.`id`,
    `EslGame`.`type`,
    `EslGame`.`date`,
    `EslGame`.`pageLink`,
    COUNT(`player`.`id`) AS 'playerCount'
FROM
    `EslGame`
        LEFT JOIN
    `EslGamePlayers` ON `EslGame`.`id` = `EslGamePlayers`.`EslGameId`
        LEFT JOIN
    `player` ON `EslGamePlayers`.`playerId` = `player`.`id`
GROUP BY `EslGame`.`id`
EOT;
    try {
      return $this->dbh->query($listGamesQuery);
    }
    catch (\Exception $e) {
      dump($e);
      return null;
    }

  }

  public function getGame($gameId)
  {
    $gameQuery = <<<EOT
SELECT
  `EslGame`.`id`,
  `EslGame`.`type`,
  `EslGame`.`date`,
  `EslGame`.`pageLink`,
  COUNT(`player`.`id`) AS 'playerCount'
FROM `EslGame`
JOIN `EslGamePlayers` ON `EslGame`.`id` = `EslGamePlayers`.`EslGameId`
JOIN `player` ON `EslGamePlayers`.`playerId` = `player`.`id`
GROUP BY `EslGame`.`id`
EOT;
    try {
      return $this->dbh->executeQuery($gameQuery, ["id" => $gameId])->fetchAll(\PDO::FETCH_ASSOC);
    }
    catch (Exception $e) {
      dump($e);
      return null;
    }
  }

  public function insertGame($link, $type, $date)
  {
    $insertQuery = <<<EOT
INSERT INTO `lethal_assassins`.`EslGame`
(`pageLink`, `type`, `date`)
VALUES (:link, :type, :date);
EOT;
    try {
      $this->insertRow($insertQuery, [
        "link" => $link,
        "type" => $type,
        "date" => $date->format('Y-m-d'),
      ]);
      return true;
    }
    catch(\Exception $e) {
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
