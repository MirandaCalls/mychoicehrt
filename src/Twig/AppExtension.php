<?php

namespace App\Twig;

use Location\Coordinate;
use Location\Distance\Vincenty;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public const METERS_PER_MILE = 1609.344;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('distance', [$this, 'calculateDistance'])
        ];
    }

    public function calculateDistance(float $aLat, float $aLong, float $bLat, float $bLong): float
    {
        $vincenty = new Vincenty();
        $a = new Coordinate($aLat, $aLong);
        $b = new Coordinate($bLat, $bLong);
        return $vincenty->getDistance($a, $b) / self::METERS_PER_MILE;
    }

}