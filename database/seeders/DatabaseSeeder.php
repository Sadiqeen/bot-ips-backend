<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Page;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserTableSeeder::class);
        $this->call(HijriTableSeeder::class);
        Page::factory(20)->has(District::factory()->count(10))->create();
    }
}
