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

      $gameResult = $this->get("model.esl")->getGame($gameId);

      if(count($gameResult) !== 1) {
        throw new NotFoundHttpException();
      }
      $game = $gameResult[0];
      $players = $this->get("model.player")->getPlayersInGame($gameId);
      $nonPlayers = $this->get("model.player")->getPlayersNotInGame($gameId);
      return $this->render('esl/game.html.twig', [
        "game" => $game,
        "players" => $players,
        "nonPlayers" => $nonPlayers,
      ]);
    }

    $gamesList = $this->get("model.esl")->getGames();

    return $this->render('esl/games-list.html.twig', [
      "gamesList" => $gamesList,
    ]);
  }

  /**
   * @Route("/change/esl-game-add", name="esl-game-add")
   * @Method("POST")
   */
  public function addGameAction(Request $request)
  {
    $dbh = $this->getDoctrine()->getManager()->getConnection();

    $this->checkCsrf($request);

    $fields = ["link", "type", "date"];
    foreach ($fields as $field) {
      if(empty($request->request->get($field))) {
        throw new BadRequestHttpException("Missing field: ".$field);
      }
    }
    $link = $request->request->get("link");
    $type = $request->request->get("type");
    $date = \DateTime::createFromFormat('Y-m-d', $request->request->get("date"));

    if($this->get("model.esl")->insertGame($link, $type, $date)) {
      $this->addFlash(
        'success',
        'Successfully added game.'
      );
    }
    else {
      $this->addFlash(
        'error',
        'FAILED TO INSERT GAME!'
      );
    }
    return $this->redirectToRoute('esl-roster', [], 302);
  }

  /**
   * @Route("/change/esl-roster-add/{gameId}", name="esl-roster-add")
   * @Method("POST")
   */
  public function rosterAddAction(Request $request, $gameId)
  {
    $dbh = $this->getDoctrine()->getManager()->getConnection();

    if(is_numeric($gameId)) {
      $this->checkCsrf($request);
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
    return new Response("Bad game ID", 400);
  }

  /**
   * @Route("/change/esl-roster-remove/{gameId}", name="esl-roster-remove")
   * @Method("POST")
   */
  public function rosterRemoveAction(Request $request, $gameId)
  {
    $dbh = $this->getDoctrine()->getManager()->getConnection();

    if(is_numeric($gameId)) {

      $this->checkCsrf($request);

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
    return new Response("Bad game ID", 400);
  }

  protected function checkCsrf($request)
  {
    $csrfTokenManager = $this->get("security.csrf.token_manager");
    if(!$csrfTokenManager->isTokenValid(new CsrfToken("change-esl", $request->request->get("csrf-token")))) {
      throw new \Exception("CSRF Token not valid", 419);
    }
  }
}
