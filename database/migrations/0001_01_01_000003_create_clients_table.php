<?php

use App\Enums\ClientStatus;
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
        Schema::create('clients', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('client_name', 150)->index();
            $table->string('company_name', 150)->nullable()->index();
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->index();
            $table->text('address')->nullable();
            $table->string('status', 20)->default(ClientStatus::Active->value);
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'deleted_at']);
            $table->index(['deleted_at', 'status', 'created_at']);
            $table->index(['created_by', 'status', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
