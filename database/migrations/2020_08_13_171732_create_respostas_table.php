<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exercicio_id');
            $table->unsignedBigInteger('jogador_id');
            $table->dateTime('ultima_interacao');
            $table->boolean('ativa');
            $table->integer('tempo');
            $table->integer('tentativas_invalidas');
            $table->integer('pontuacao');
            $table->integer('repeticao');
            $table->boolean('concluida');
            $table->timestamps();
            $table->foreign('exercicio_id')->references('id')->on('exercicios');
            $table->foreign('jogador_id')->references('id')->on('jogadores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('respostas_mvflp');
    }
}
