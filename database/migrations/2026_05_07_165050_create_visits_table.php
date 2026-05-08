<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('city')->nullable();
            $table->string('device', 20);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('city');
            $table->index('device');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
