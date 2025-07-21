<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->enum('status', ['active', 'inactive', 'pending']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
}; 