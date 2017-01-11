<?php

namespace AppBundle\services;

use Doctrine\DBAL\Connection;

use Psr\Log\LoggerInterface;

/**
 *
 */
class EslModel
{
  protected $dbh;
  protected $logger;

  public function __construct(Connection $dbh, LoggerInterface $logger)
  {
    $this->dbh = $dbh;
    $this->logger = $logger;
  }

  protected function error($message)
  {
    $this->logger->error("EslModel Error: ".$message);
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
      $this->error($e->getMessage());
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
LEFT JOIN `EslGamePlayers` ON `EslGame`.`id` = `EslGamePlayers`.`EslGameId`
LEFT JOIN `player` ON `EslGamePlayers`.`playerId` = `player`.`id`
WHERE `EslGame`.`id` = :id
GROUP BY `EslGame`.`id`
EOT;
    try {
      return $this->dbh->executeQuery($gameQuery, ["id" => $gameId])->fetchAll(\PDO::FETCH_ASSOC);
    }
    catch (Exception $e) {
      $this->error($e->getMessage());
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
      $this->error($e->getMessage());
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
