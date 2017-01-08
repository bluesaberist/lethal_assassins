<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use AppBundle\Utility\RankUtils;

class PlayerController extends Controller
{

  /**
   * @Route("/admin/show-players", name="admin-show-players")
   */
  public function showPlayersAction()
  {
    $players = $this->get("model.player")->getPlayers();
    foreach ($players as &$player) {
      $player["rankName"] = RankUtils::rankToText($player["rank"]);
    }
    $inactivePlayers = [];
    dump($players);
    return $this->render('player/show-players.html.twig', [
      "players" => $players,
      "inactivePlayers" => $inactivePlayers,
    ]);
  }

  /**
   * @Route("/admin/show-player/{id}", name="admin-show-player")
   */
  public function showPlayerAction($id)
  {
    if(!is_numeric($id)) {
      throw new BadRequestHttpException("Invalid ID");
    }
    $player = $this->get("model.player")->getPlayer($id);
    if(!$player) {
      throw new NotFoundHttpException("Player not found.");
    }
    $player["rank"] = RankUtils::rankToText($player["rank"]);
    return $this->render('player/show-player.html.twig', [
      "player" => $player,
    ]);
  }

  /**
   * @Route("/admin/edit-player/{id}", name="admin-edit-player")
   */
  public function editPlayerAction(Request $request, $id)
  {
    if(!is_numeric($id)) {
      throw new BadRequestHttpException("Invalid ID");
    }
    $player = $this->get("model.player")->getPlayer($id);
    if(!$player) {
      throw new NotFoundHttpException("Player not found.");
    }
    if($request->getMethod() === "POST") {
      $fields = [];
      $inputRank = (int)$request->request->get("rank");
      if(isset(RankUtils::RANKS[$inputRank])) {
        $fields["rank"] = $inputRank;
      }
      $fields["name"] = $request->request->get("name");
      $fields["joindate"] = $request->request->get("joindate");
      $fields["discordId"] = $request->request->get("discordId");
      $fields["email"] = $request->request->get("email");
      $fields["active"] = $request->request->get("active") == null ? false : true;
      if($this->get("model.player")->editPlayer($id, $fields)) {
        $this->addFlash(
          'success',
          'Updated player.'
        );
        return $this->redirectToRoute('admin-show-player', ["id" => $id], 302);
      }
      else {
        $this->addFlash(
          'error',
          'FAILED TO UPDATE PLAYER!'
        );
      }
    }

    return $this->render('player/edit-player.html.twig', [
      "player" => $player,
      "ranks" => RankUtils::RANKS,
    ]);
  }

  /**
   * @Route("/admin/disable-player", name="admin-disable-player")
   */
  public function disablePlayerAction(Request $request)
  {

  }
}
