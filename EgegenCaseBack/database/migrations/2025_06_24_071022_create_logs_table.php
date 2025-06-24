<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip')->nullable()->comment('IP adresi');
            $table->string('method')->nullable()->comment('HTTP metodu (GET, POST, vs.)');
            $table->string('path')->nullable()->comment('İstek yapılan URL path\'i');
            $table->text('request_body')->nullable()->comment('İstek gövdesi (JSON formatında)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
