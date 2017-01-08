<?php

namespace AppBundle\Utility;

class RankUtils
{
  const RANKS = [
    1 => "Clan Master",
    2 => "Commander",
    3 => "Officer",
    4 => "Fighter",
    13 => "Recruit",
    -1 => "Not Active",
  ];

  public static function rankToText($id)
  {
    return isset(RankUtils::RANKS[$id]) ? RankUtils::RANKS[$id] : "";
  }
}
