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
        Schema::connection('db_programacion')->create('cohorts', function (Blueprint $table) {
            $table->id();
            //VERIFICAR NIVEL DE FICHA O DE PROGRAMA
            $table->foreignId('id_program')->constrained('programs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_instructor')->constrained('instructors')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_time')->constrained('cohort_time')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_classroom')->constrained('classrooms')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_town')->constrained('towns')->onUpdate('cascade')->onDelete('cascade');
            
            $table->integer('hours_school_stage');
            $table->integer('hours_practical_stage');
            
            $table->date('start_date_school_stage');
            $table->date('end_date_school_stage');

            $table->date('start_date_practical_stage');
            $table->date('end_date_practical_stage');
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
