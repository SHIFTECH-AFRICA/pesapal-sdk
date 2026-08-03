<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('merchant_reference', 50)->unique();
            $table->uuid('pesapal_tracking_id')->nullable()->unique();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('KES');
            $table->string('status', 20)->default('PENDING')->index();
            $table->string('payment_method')->nullable();
            $table->string('confirmation_code')->nullable()->index();
            $table->string('payment_account')->nullable();
            $table->text('redirect_url')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
