<?php

namespace App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores;

use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\Formula;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\PassoDerivacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\PassoFechamento;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\PassoInicializacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\PassoTicagem;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\TentativaDerivacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\TentativaFechamento;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\TentativaInicializacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\TentativaTicagem;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\GeradorArvore;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Manipuladores\FecharNo;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Manipuladores\FecharTodosNos;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Manipuladores\TicarNo;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Manipuladores\TicarTodosNos;

class GeradorPorPasso extends GeradorArvore
{
    /**
     * Reconstroi a arvore atraves da listas de passos já
     * executados e tenta inserir o novo passo.
     * @param Formula              $formula
     * @param PassoInicializacao[] $lista
     * @param PassoInicializacao   $novoPasso
     * @param array
     * @param  array                  $passosExecutados
     * @return TentativaInicializacao
     */
    public function reconstruirInicializacao(Formula $formula, array $passosExecutados, ?PassoInicializacao $novoPasso = null): TentativaInicializacao
    {
        foreach ($passosExecutados as $passo) {
            $tentativa = $this->inserirNoIncializacao($formula, $passo->getIdNo(), $passo->getNegacao());

            if (!$tentativa->getSucesso()) {
                return  $tentativa;
            }
        }

        if (is_null($novoPasso)) {
            return new TentativaInicializacao([
                'sucesso'  => true,
                'mensagem' => 'sucesso',
                'arvore'   => $this->arvore,
                'passos'   => $passosExecutados,
            ]);
        }

        $tentativa = $this->inserirNoIncializacao($formula, $novoPasso->getIdNo(), $novoPasso->getNegacao());

        if (!$tentativa->getSucesso()) {
            return  $tentativa;
        }

        array_push($passosExecutados, $novoPasso);
        return new TentativaInicializacao([
            'sucesso'  => true,
            'mensagem' => 'sucesso',
            'arvore'   => $this->arvore,
            'passos'   => $passosExecutados,
        ]);
    }

    /**
     * Esta função tem a finalidade resconstruir os passo ja executados e
     * validar e derivar a tentativa do usuario.
     * @param  PassoDerivacao[]   $passosExecutados
     * @param  PassoDerivacao     $passo
     * @param  ?PassoDerivacao    $passoNovo
     * @return TentativaDerivacao
     */
    public function reconstruirArvore(array $passosExecutados, ?PassoDerivacao $passoNovo = null): TentativaDerivacao
    {
        foreach ($passosExecutados as $exec) {
            $tentativa = $this->derivar($exec);

            if (!$tentativa->getSucesso()) {
                return  $tentativa;
            }
        }

        if (is_null($passoNovo)) {
            return new TentativaDerivacao([
                'sucesso'  => true,
                'mensagem' => 'sucesso',
                'arvore'   => $this->arvore,
                'passos'   => $passosExecutados,
            ]);
        }

        $tentativa = $this->derivar($exec);

        if (!$tentativa->getSucesso()) {
            return  $tentativa;
        }

        return new TentativaDerivacao([
            'sucesso'  => true,
            'mensagem' => 'sucesso',
            'arvore'   => $this->arvore,
            'passos'   => [...$passosExecutados, ...$tentativa->getPassos()],
        ]);
    }

    /**
     *
     * @param  PassoTicagem[]   $passosExecutados
     * @param  ?PassoTicagem    $novoPasso
     * @return TentativaTicagem
     */
    public function reconstruirTicagem(array $passosExecutados, ?PassoTicagem $novoPasso = null): TentativaTicagem
    {
        $tentativa = TicarTodosNos::exec($this->arvore, $passosExecutados);

        if (!$tentativa->getSucesso() || is_null($novoPasso)) {
            return $tentativa;
        }

        $tentativa = TicarNo::exec($this->arvore, $novoPasso);

        if (!$tentativa->getSucesso()) {
            return  $tentativa;
        }

        return new TentativaDerivacao([
            'sucesso'  => true,
            'mensagem' => 'sucesso',
            'arvore'   => $this->arvore,
            'passos'   => [...$passosExecutados, ...$tentativa->getPassos()],
        ]);
    }

        /**
         *
         * @param  PassoFechamento[]   $passosExecutados
         * @param  ?PassoFechamento    $novoPasso
         * @return TentativaFechamento
         */
    public function reconstruirFechamento(array $passosExecutados, ?PassoFechamento $novoPasso = null): TentativaFechamento
    {
        $tentativa = FecharTodosNos::exec($this->arvore, $passosExecutados);

        if (!$tentativa->getSucesso() || is_null($novoPasso)) {
            return $tentativa;
        }

        $tentativa = FecharNo::exec($this->arvore, $novoPasso);

        if (!$tentativa->getSucesso()) {
            return  $tentativa;
        }

        return new TentativaDerivacao([
            'sucesso'  => true,
            'mensagem' => 'sucesso',
            'arvore'   => $this->arvore,
            'passos'   => [...$passosExecutados, ...$tentativa->getPassos()],
        ]);
    }
}
