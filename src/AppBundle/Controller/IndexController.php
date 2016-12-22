<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{
  /**
   * @Route("/", name="homepage")
   */
  public function indexAction(Request $request)
  {

    $dbh = $this->getDoctrine()->getManager()->getConnection();

    $memberQuery = "SELECT `id`, `rank`, `name`, `joindate`, `discordid` FROM player WHERE rank = 1 ORDER BY `joindate`";
    $clanMaster = $dbh->query($memberQuery);

    $memberQuery = "SELECT `id`, `rank`, `name`, `joindate`, `discordid` FROM player WHERE rank = 2 OR rank = 3 ORDER BY rank ASC, `joindate`";
    $officer = $dbh->query($memberQuery);

    $memberQuery = "SELECT `id`, `rank`, `name`, `joindate`, `discordid` FROM player WHERE rank = 4 ORDER BY `joindate`";
    $fighter = $dbh->query($memberQuery);

    $memberQuery = "SELECT `id`, `rank`, `name`, `joindate`, `discordid` FROM player WHERE rank = 13 ORDER BY `joindate`";
    $recruit = $dbh->query($memberQuery);

    $screenshots = [
      "https://i.imgur.com/WuepXLd.jpg",
      "https://i.imgur.com/Mkz1oMj.jpg",
      "https://i.imgur.com/sMo48zc.jpg",
      "https://i.imgur.com/QMo1A9R.jpg",
      "https://i.imgur.com/YLBbmBD.jpg",
      "https://i.imgur.com/3Iqrxkg.jpg",
      "https://i.imgur.com/43T8O96.jpg",
      "https://i.imgur.com/NvP0BVW.jpg",
      "https://i.imgur.com/JlVskZt.jpg",
      "https://i.imgur.com/IsZqwQW.jpg",
      "https://i.imgur.com/nOmAsvp.jpg",
      "https://i.imgur.com/e8cdv5g.jpg",
      "https://i.imgur.com/aEM8OKe.jpg",
      "https://i.imgur.com/ArxUbnx.jpg",
      "https://i.imgur.com/VBV9Ocg.jpg",
      "https://i.imgur.com/FPsxaxf.jpg",
      "https://i.imgur.com/Avm7zQE.jpg",
      "https://i.imgur.com/X5dzXw3.jpg",
      "https://i.imgur.com/1qb5di1.jpg",
      "https://i.imgur.com/hISbQbR.jpg",
      "https://i.imgur.com/vOVN031.jpg",
    ];

    return $this->render('index.html.twig', [
      "clanMaster" => $clanMaster,
      "officers" => $officer,
      "fighters" => $fighter,
      "recruits" => $recruit,
      "screenshots" => $screenshots,
    ]);
  }
}
