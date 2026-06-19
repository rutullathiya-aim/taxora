<?php

use App\Enums\ProjectStatus;
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
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('project_code', 30)->unique();
            $table->foreignUlid('client_id')->constrained()->cascadeOnDelete();
            $table->string('project_name', 150)->index();
            $table->text('description')->nullable();
            $table->foreignUlid('service_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default(ProjectStatus::Active->value);
            $table->date('due_date')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('deleted_at');
            $table->index(['status', 'created_at']);
            $table->index(['client_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
