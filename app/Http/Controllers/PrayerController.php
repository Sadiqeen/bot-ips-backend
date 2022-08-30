<?php

namespace App\Http\Controllers;

use IslamicNetwork\PrayerTimes\PrayerTimes;
use IslamicNetwork\PrayerTimes\Method;

class PrayerController extends Controller
{

    /**
     * Get Prayer Time
     *
     * @param $data $data [explicite description]
     *
     * @return array
     */
    public static function GetPrayerTime($data, $date): array
    {

        $method = new Method('My Custom Method');
        $method->setFajrAngle(19);
        $method->setIshaAngleOrMins(18);

        // And then:
        $pt = new PrayerTimes(Method::METHOD_CUSTOM);
        $pt->setCustomMethod($method);
        // Adjust time
        $pt->tune($imsak = 1, $fajr = -1, $sunrise = 1, $dhuhr = 3, $asr = 0, $maghrib = 2, $sunset = 0, $isha = 2, $midnight = 0);
        // And then the same as before:
        // $times = $pt->getTimesForToday((float)$data->lat, (float)$data->long, "Asia/Bangkok", $elevation = null, $latitudeAdjustmentMethod = PrayerTimes::LATITUDE_ADJUSTMENT_METHOD_ANGLE, $midnightMode = PrayerTimes::MIDNIGHT_MODE_STANDARD, $format = PrayerTimes::TIME_FORMAT_24H);
        $times = $pt->getTimes($date, (float)$data->lat, (float)$data->long);
        return $times;
    }
}
