<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('body');
            $table->string('customer_tier'); // Enterprise, Premium, Standard
            $table->string('product_area');  // Billing, Auth, Database, UI

            // ML Triage & Evaluation Fields
            $table->string('human_priority');      // Training Label (P1, P2, P3, P4)
            $table->string('predicted_priority')->nullable(); // Computed asynchronously

            // Vector Storage for Stretch Goal
            $table->longText('body_vector')->nullable(); // Stores embedding matrix array

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
