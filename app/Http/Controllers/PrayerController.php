<?php

namespace App\Http\Controllers;

use App\Models\Adjust;
use IslamicNetwork\PrayerTimes\PrayerTimes;
use IslamicNetwork\PrayerTimes\Method;

class PrayerController extends Controller
{

    /**
     * Get Prayer Time
     *
     * @param $data object [explicite description]
     *
     * @return array
     */
    public function GetPrayerTime($data, $date): array
    {
        $adjust = Adjust::find(1)->toArray();

        return $this->calPrayerTime(
            $adjust,
            $date,
            $data->lat,
            $data->long,
            $adjust["method"]
        );
    }

    public static function calPrayerTime($adjust, $date, $lat, $long, $method)
    {
        $pt = new PrayerTimes($method);
        // Adjust time
        $pt->tune(
            $adjust["imsak"],
            $adjust["fajr"],
            $adjust["sunrise"],
            $adjust["dhuhr"],
            $adjust["asr"],
            $adjust["maghrib"],
            $adjust["sunset"],
            $adjust["isha"],
            $adjust["midnight"]
        );

        $times = $pt->getTimes($date, (float)$lat, (float)$long);
        return $times;
    }
}
