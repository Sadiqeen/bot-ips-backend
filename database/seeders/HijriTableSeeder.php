<?php

namespace Database\Seeders;

use App\Models\Hijri;
use Illuminate\Database\Seeder;

class HijriTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Hijri::create([
            'month_num' => 4,
            'month_th' => "รอบีอุ้ลอาเคร",
            'year' => 1443,
            'international' => "2021-11-07",
        ]);

        Hijri::create([
            'month_num' => 5,
            'month_th' => "ยะมาดิ้ลเอาวาล",
            'year' => 1443,
            'international' => "2021-12-06",
        ]);

        Hijri::create([
            'month_num' => 6,
            'month_th' => "ยะมาดิ้ลอาเคร",
            'year' => 1443,
            'international' => "2022-01-04",
        ]);

        Hijri::create([
            'month_num' => 7,
            'month_th' => "รอยับ",
            'year' => 1443,
            'international' => "2022-02-03",
        ]);

        Hijri::create([
            'month_num' => 8,
            'month_th' => "ชะบาน",
            'year' => 1443,
            'international' => "2022-03-05",
        ]);

        Hijri::create([
            'month_num' => 9,
            'month_th' => "รอมฎอน",
            'year' => 1443,
            'international' => "2022-04-03",
        ]);

        Hijri::create([
            'month_num' => 10,
            'month_th' => "เชาวาล",
            'year' => 1443,
            'international' => "2022-05-02",
        ]);

        Hijri::create([
            'month_num' => 11,
            'month_th' => "ซุ้ลเกาะดะห์",
            'year' => 1443,
            'international' => "2022-06-01",
        ]);
    }
}
