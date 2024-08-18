<?php

use Database\Seeders\AdjustSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adjusts', function (Blueprint $table) {
            $table->id();
            $table->integer('imsak')->default(0);
            $table->integer('fajr')->default(0);
            $table->integer('sunrise')->default(0);
            $table->integer('dhuhr')->default(0);
            $table->integer('asr')->default(0);
            $table->integer('maghrib')->default(0);
            $table->integer('sunset')->default(0);
            $table->integer('isha')->default(0);
            $table->integer('midnight')->default(0);
            $table->string('method')->default("KARACHI");

            $table->unsignedBigInteger('page_id')->nullable();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->timestamps();
        });

        $seeder = new AdjustSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjusts');
    }
};
