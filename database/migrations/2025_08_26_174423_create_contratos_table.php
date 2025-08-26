<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id('id_contrato');

            // Relación 1:1 con reservaciones
            $table->foreignId('id_reservacion')
                  ->constrained('reservaciones', 'id_reservacion')
                  ->onDelete('cascade')
                  ->unique();

            $table->string('numero_contrato', 100)->nullable();

            // Placeholders como BLOB; luego los convertimos a LONGBLOB (MySQL)
            $table->binary('contrato_word')->nullable();   // .docx
            $table->binary('contrato_pdf')->nullable();    // .pdf
            $table->string('contrato_word_mime', 50)->nullable();
            $table->string('contrato_pdf_mime', 50)->nullable();

            $table->binary('factura_tentativa_word')->nullable();   // .docx
            $table->binary('factura_tentativa_excel')->nullable();  // .xlsx
            $table->string('factura_tentativa_word_mime', 50)->nullable();
            $table->string('factura_tentativa_excel_mime', 50)->nullable();

            $table->enum('estado', ['borrador','emitido','cancelado'])->default('borrador');
            $table->text('notas')->nullable();

            $table->timestamps();

            $table->index(['estado']);
            $table->index(['numero_contrato']);
        });

        // Convertir columnas binarias a LONGBLOB (MySQL)
        DB::statement('ALTER TABLE contratos
            MODIFY contrato_word LONGBLOB NULL,
            MODIFY contrato_pdf LONGBLOB NULL,
            MODIFY factura_tentativa_word LONGBLOB NULL,
            MODIFY factura_tentativa_excel LONGBLOB NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
