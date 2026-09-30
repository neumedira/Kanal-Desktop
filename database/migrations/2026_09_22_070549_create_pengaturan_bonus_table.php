<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengaturan_bonus', function (Blueprint $table) {
            $table->id();
            $table->integer('minimal_views')->default(3000);
            $table->decimal('nominal_bonus', 15, 2)->default(50000);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengaturan_bonus');
    }
};