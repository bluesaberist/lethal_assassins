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
