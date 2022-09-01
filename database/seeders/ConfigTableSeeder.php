<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::create([
            'name' => 'Telegram ID',
            'key' => 'telegram_id',
            'value' => '1349970929'
        ]);

        Config::create([
            'name' => 'Telegram TOKEN',
            'key' => 'telegram_token',
            'value' => '5536084464:AAGVcypL4xP6bMC4HX9mRjTdQiGSpBU3m6k'
        ]);

        Config::create([
            'name' => 'Post TOKEN',
            'key' => 'post_token',
            'value' => '3eDLlk6qSzDZFWqzRDO1Ql64aQrLd0hryF04JV6eGq3PQKBeu'
        ]);
    }
}
