<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Page;

class MessageController extends Controller
{

    /**
     * Build full message
     *
     * @param $data object [explicite description]
     *
     * @return string
     */
    public function buildMessage($data, $update = false) : string
    {
        $message = $this->headerMesseage($update);
        $index = 1;
        $tomorrow = Carbon::tomorrow('Asia/Bangkok')->toDateTime();

        $message .= "------------------------------------\n\n";
        $message .= "ปฏิทินอิสลามประเทศไทย https://islamic-calendar.sleepless-tech.com \n\n";

        foreach ($data->districtDesc as $district) {
            $time = (new PrayerController)->GetPrayerTime($district, $tomorrow);

            if ($index === 1) {
                $message .= $this->mainMessage($time, $district);
            } else {
                $message .= $this->shortMessage($time, $district);
            }

            $index++;
        }

        return $message;
    }

    /**
     * Header Message
     *
     * @return string
     */
    private function headerMesseage($update = false) : string
    {
        $date = Carbon::tomorrow();
        $message =  "เวลาละหมาดประจำวัน" . $date->locale("th")->translatedFormat("D") . " ที่ " . MessageController::GenDate($date) . "\n\n";

        $islamicDay =  (new HijriController)->extendDate(1);
        $islamicDate = explode(" ", $islamicDay);

        if ($islamicDate[0] < 30 || $update) {

            $message .= "ตรงกับวัน" . $date->locale("th")->translatedFormat("D") . " ที่ " . $islamicDay . "\n\n";

        } else {

            $message .= "รอยืนยันผลการดูดวงจันทร์\n\n";

        }

        return $message;
    }


    /**
     * Vertical message
     *
     * @param array $time [explicite description]
     * @param $districts object [explicite description]
     *
     * @return string
     */
    private function mainMessage(array $time, $districts) : string
    {
        $message =  "------------------------------------\n\n" .
        "อำเภอ " . $districts->name . "\n\n" .
        "ซุบฮิ : " . $time["Fajr"] . "\n\n" .
        "ชูรุก : " . $time["Sunrise"] . "\n\n" .
        "ซุฮฺริ : " . $time["Dhuhr"] . "\n\n" .
        "อัศริ : " . $time["Asr"] . "\n\n" .
        "มักริบ : " . $time["Maghrib"] . "\n\n" .
        "อีซา : " . $time["Isha"] . "\n\n";

        return $message;
    }

    /**
     * Horizontal message
     *
     * @param array $time [explicite description]
     * @param $districts object [explicite description]
     *
     * @return string
     */
    private function shortMessage(array $time, $districts) : string
    {
        $message =  "------------------------------------\n\n" .
        "อำเภอ " . $districts->name . "\n\n" .
        "ซุบฮิ : " . $time["Fajr"] . " | " .
        "ชูรุก : " . $time["Sunrise"] . " | " .
        "ซุฮฺริ : " . $time["Dhuhr"] . "\n\n" .
        "อัศริ : " . $time["Asr"] . " | " .
        "มักริบ : " . $time["Maghrib"] .  " | " .
        "อีซา : " . $time["Isha"] . "\n\n";

        return $message;
    }

    /**
     * List of Facebook Page
     *
     * @return string
     */
    public static function otherPageMessage() : string
    {
        $pages = Page::select(["name", "url"])->has("district")->get();

        $message = "------------------------------------\n\n" .
        "เพจเวลาละหมาดสำหรับจังหวัดต่างๆ\n\n" .
        "------------------------------------" . "\n\n";

        foreach ($pages as $page) {
            $message .= $page->name . "\n" .  $page->url . "\n\n";
        }

        return $message;
    }

    public static function GenDate($date)
    {
        $thai_month =  [
            "1" => "มกราคม",
            "2" => "กุมภาพันธ์",
            "3" => "มีนาคม",
            "4" => "เมษายน",
            "5" => "พฤษภาคม",
            "6" => "มิถุนายน",
            "7" => "กรกฎาคม",
            "8" => "สิงหาคม",
            "9" => "กันยายน",
            "10" => "ตุลาคม",
            "11" => "พฤศจิกายน",
            "12" => "ธันวาคม"
        ];

        return $date->format("d") . " " . $thai_month[$date->format("n")] . " " . $date->format("Y");
    }

}
