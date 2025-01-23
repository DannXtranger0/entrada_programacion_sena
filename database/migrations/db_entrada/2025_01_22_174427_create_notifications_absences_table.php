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
        Schema::connection('db_entrada')->create('notifications_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_person')->constrained('people')->onUpdate('cascade');
            $table->date('last_assistance');
            $table->text('motive')->nullable();
            $table->enum('state',['pendiente','respondida']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_absences');
    }
};
