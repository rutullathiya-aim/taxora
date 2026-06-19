<?php

use App\Enums\ServiceChecklistItemStatus;
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
        Schema::create('service_checklist_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('service_id')->constrained('services')->cascadeOnDelete();
            $table->text('title');
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default(ServiceChecklistItemStatus::Active->value);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['service_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_checklist_items');
    }
};
