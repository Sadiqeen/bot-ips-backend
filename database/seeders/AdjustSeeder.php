<?php

namespace Database\Seeders;

use App\Models\Adjust;
use Illuminate\Database\Seeder;

class AdjustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Adjust
        Adjust::create([
            'id' => 1,
            'imsak' => 0,
            'fajr' => 0,
            'sunrise' => 0,
            'dhuhr' => 0,
            'asr' => 0,
            'maghrib' => 0,
            'sunset' => 0,
            'isha' => 0,
            'midnight' => 0,
        ]);
    }
}
