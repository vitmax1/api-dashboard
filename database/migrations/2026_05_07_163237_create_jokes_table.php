<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jokes', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique();
            $table->string('type');
            $table->text('setup');
            $table->text('punchline');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jokes');
    }
};
