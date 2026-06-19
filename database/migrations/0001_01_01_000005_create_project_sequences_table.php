<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_sequences', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedBigInteger('last_number')->default(0);
        });

        DB::table('project_sequences')->insert([
            'id' => 1,
            'last_number' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_sequences');
    }
};
