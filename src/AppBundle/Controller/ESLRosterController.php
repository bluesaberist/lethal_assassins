<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;

class ESLRosterController extends Controller
{
  /**
   * @Route("/esl-roster/{gameId}", name="esl-roster")
   */
  public function rosterAction(Request $request, $gameId = null)
  {
    $dbh = $this->getDoctrine()->getManager()->getConnection();

    if(is_numeric($gameId)) {

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
      $playersQuery = <<<EOT
SELECT
  `player`.`id`,
  `player`.`rank`,
  `player`.`name`,
  `player`.`discordid`,
  `EslGamePlayers`.`commitment`
FROM `EslGame`
  JOIN `EslGamePlayers` ON `EslGame`.`id` = `EslGamePlayers`.`EslGameId`
  JOIN `player` ON `EslGamePlayers`.`playerId` = `player`.`id`
WHERE `EslGame`.`id` = :id
ORDER BY `player`.`rank` ASC, `player`.`name`
EOT;

      $gameResult = $dbh->executeQuery($gameQuery, ["id" => $gameId])->fetchAll(\PDO::FETCH_ASSOC);

      if(count($gameResult) !== 1) {
        throw new NotFoundHttpException();
      }

      $game = $gameResult[0];

      $players = $dbh->executeQuery($playersQuery, ["id" => $gameId])->fetchAll(\PDO::FETCH_ASSOC);

      return $this->render('esl/game.html.twig', [
        "game" => $game,
        "players" => $players,
      ]);
    }

    $listGamesQuery = <<<EOT
SELECT
    `EslGame`.`id`,
    `EslGame`.`type`,
    `EslGame`.`date`,
    `EslGame`.`pageLink`,
    COUNT(`player`.`id`) AS 'playerCount'
FROM
    `EslGame`
        JOIN
    `EslGamePlayers` ON `EslGame`.`id` = `EslGamePlayers`.`EslGameId`
        JOIN
    `player` ON `EslGamePlayers`.`playerId` = `player`.`id`
GROUP BY `EslGame`.`id`
EOT;
    $gamesList = $dbh->query($listGamesQuery);

    return $this->render('esl/games-list.html.twig', [
      "gamesList" => $gamesList,
    ]);
  }

  /**
   * @Route("/esl-roster-add/{gameId}", name="esl-roster-add")
   * @Method("POST")
   */
  public function rosterAddAction(Request $request, $gameId)
  {
    $dbh = $this->getDoctrine()->getManager()->getConnection();

    if(is_numeric($gameId)) {
      if($request->getMethod() === "POST") {
        $csrfTokenManager = $this->get("security.csrf.token_manager");
        if(!$csrfTokenManager->isTokenValid(new CsrfToken("esl-roster-edit", $request->request->get("csrl-token")))) {
          return new Response("CSRF Token not valid", 401);
        }
        $playerId = $request->request->get("playerId");
        if(!is_numeric($playerId)) {
          throw new BadRequestHttpException("Missing player ID");
        }
        $playerId = $playerId + 0;
        $commitment = $request->request->get("playerCommitment");
        if(empty($commitment)) {
          $commitment = "";
        }

        $insertRecordQuery = <<<EOT
INSERT INTO `lethal_assassins`.`EslGamePlayers`
	(`playerId`, `EslGameId`, `commitment`)
VALUES (:playerId, :gameId, :commitment)
EOT;

        try {
          $statement = $dbh->executeQuery($insertRecordQuery, [
            "gameId" => $gameId,
            "playerId" => $playerId,
            "commitment" => $commitment,
          ]);
          if($statement->rowCount() !== 1) {
            throw new \Exception();
          }
          $this->addFlash(
            'success',
            'Successfully inserted player.'
          );
        }
        catch(\Exception $ex) {
          $this->addFlash(
            'error',
            'FAILED TO INSERT PLAYER!'
          );
        }
        return $this->redirectToRoute('esl-roster', ["gameId" => $gameId], 302);
      }
    }
    return new Response("Bad game ID", 400);
  }

  /**
   * @Route("/esl-roster-remove/{gameId}", name="esl-roster-remove")
   * @Method("POST")
   */
  public function rosterRemoveAction(Request $request, $gameId)
  {
    $dbh = $this->getDoctrine()->getManager()->getConnection();

    if(is_numeric($gameId)) {
      if($request->getMethod() === "POST") {
        $csrfTokenManager = $this->get("security.csrf.token_manager");
        if(!$csrfTokenManager->isTokenValid(new CsrfToken("esl-roster-edit", $request->request->get("csrl-token")))) {
          return new Response("CSRF Token not valid", 401);
        }
        $playerId = $request->request->get("playerId");
        if(!is_numeric($playerId)) {
          throw new BadRequestHttpException("Missing player ID");
        }
        $playerId = $playerId + 0;

        $deleteRecordQuery = <<<EOT
DELETE FROM `lethal_assassins`.`EslGamePlayers`
WHERE `EslGameId` = :gameId AND `playerId` = :playerId
EOT;

        try {
          $statement = $dbh->executeQuery($deleteRecordQuery, [
            "gameId" => $gameId,
            "playerId" => $playerId,
          ]);
          if($statement->rowCount() !== 1) {
            throw new \Exception();
          }
          $this->addFlash(
            'success',
            'Successfully removed player.'
          );
        }
        catch(\Exception $ex) {
          $this->addFlash(
            'error',
            'FAILED TO DELETE PLAYER!'
          );
        }
        return $this->redirectToRoute('esl-roster', ["gameId" => $gameId], 302);
      }
    }
    return new Response("Bad game ID", 400);
  }
}
