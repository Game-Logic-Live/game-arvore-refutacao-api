<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormulasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('formula');
            $table->text('xml');
            $table->integer('quantidade_regras');
            $table->boolean('ticar_automaticamente');
            $table->boolean('fechar_automaticamente');
            $table->boolean('inicio_personalizado');
            $table->text('lista_passos')->nullable();
            $table->text('lista_derivacoes')->nullable();
            $table->text('lista_ticagem')->nullable();
            $table->text('lista_fechamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formulas');
    }
}
