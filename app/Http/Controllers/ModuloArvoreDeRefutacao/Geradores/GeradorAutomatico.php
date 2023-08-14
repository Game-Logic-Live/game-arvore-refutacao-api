<?php

namespace App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores;

use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\Formula;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\No;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\PassoDerivacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Common\Models\Geradores\TentativaDerivacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Buscadores\EncontraDuplaNegacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Buscadores\EncontraNoBifurca;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Buscadores\EncontraNoSemBifucacao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\Buscadores\EncontraProximoNoParaInsercao;
use App\Http\Controllers\ModuloArvoreDeRefutacao\Geradores\Common\GeradorArvore;

class GeradorAutomatico extends GeradorArvore
{
    /**
     * Esta função gera e retorna as primeiras linhas da arvores de refutacao
     * @param  Formula $formula
     * @return No|null
     */
    public function inicializar(Formula $formula): ?No
    {
        $ultimoNo = null;
        $premissas = $formula->getPremissas();
        $conclusao = $formula->getConclusao();

        if (!empty($premissas)) {
            $premissa = array_pop($premissas);

            $this->arvore = new No($this->genereteIdNo(), $premissa->getValorObjPremissa(), null, null, null, 1, null, null, false, false);
            $ultimoNo = $this->arvore;

            foreach ($premissas as $premissa) {
                $ultimoNo->setFilhoCentroNo(new No($this->genereteIdNo(), $premissa->getValorObjPremissa(), null, null, null, $ultimoNo->getLinhaNo() + 1, null, null, false, false));
                $ultimoNo = $ultimoNo->getFilhoCentroNo();
            }
        }

        $conclusao->getValorObjConclusao()->addNegacaoPredicado();

        if ($this->arvore == null) {
            $this->arvore = (new No($this->genereteIdNo(), $conclusao->getValorObjConclusao(), null, null, null, 1, null, null, false, false));
            $ultimoNo = $this->arvore;
        } else {
            $ultimoNo->setFilhoCentroNo(new No($this->genereteIdNo(), $conclusao->getValorObjConclusao(), null, null, null, $ultimoNo->getLinhaNo() + 1, null, null, false, false));
            $ultimoNo = $ultimoNo->getFilhoCentroNo();
        }

        return $this->arvore;
    }

    /**
     * Cria a arvore otimizada
     * @return ?TentativaDerivacao
     */
    public function arvoreOtimizada(): ?TentativaDerivacao
    {
        $noInsercao = EncontraProximoNoParaInsercao::exec($this->arvore);

        if ($noInsercao == null) {
            return  new TentativaDerivacao([
                'sucesso'  => true,
                'mensagem' => 'sucesso',
                'arvore'   => $this->arvore,
                'passos'   => [],
            ]);
        } else {
            $no = EncontraDuplaNegacao::exec($this->arvore, $noInsercao);
            $noBifur = EncontraNoBifurca::exec($this->arvore, $noInsercao);
            $noSemBifur = EncontraNoSemBifucacao::exec($this->arvore, $noInsercao);

            if (!is_null($no)) {
                $qntdNegado = $no->getValorNo()->getNegadoPredicado();
                $regra = $no->getValorNo()->getTipoPredicado()->regra($qntdNegado);
                $passo = new PassoDerivacao([
                    'idNoDerivacao'  => $no->getIdNo(),
                    'idsNoInsercoes' => [$noInsercao->getIdNo()],
                    'regra'          => $regra,
                ]);
            } elseif (!is_null($noSemBifur)) {
                $qntdNegado = $noSemBifur->getValorNo()->getNegadoPredicado();
                $regra = $noSemBifur->getValorNo()->getTipoPredicado()->regra($qntdNegado);
                $passo = new PassoDerivacao([
                    'idNoDerivacao'  => $noSemBifur->getIdNo(),
                    'idsNoInsercoes' => [$noInsercao->getIdNo()],
                    'regra'          => $regra,
                ]);
            } elseif (!is_null($noBifur)) {
                $qntdNegado = $noBifur->getValorNo()->getNegadoPredicado();
                $regra = $noBifur->getValorNo()->getTipoPredicado()->regra($qntdNegado);
                $passo = new PassoDerivacao([
                    'idNoDerivacao'  => $noBifur->getIdNo(),
                    'idsNoInsercoes' => [$noInsercao->getIdNo()],
                    'regra'          => $regra,
                ]);
            }

            if (isset($passo)) {
                $tentativa = $this->derivar($passo);

                if (!$tentativa->getSucesso()) {
                    return  $tentativa;
                }

                return $this->arvoreOtimizada();
            }

            return  new TentativaDerivacao([
                'sucesso'  => true,
                'mensagem' => 'sucesso',
                'arvore'   => $this->arvore,
                'passos'   => [],
            ]);
            ;
        }
    }

    /**
     * Cria a pior arvore possivel
     * @param  No                 $arvore
     * @return TentativaDerivacao
     */
    public function piorArvore(): TentativaDerivacao
    {
        $noInsercao = EncontraProximoNoParaInsercao::exec($this->arvore);

        if ($noInsercao == null) {
            return $this->arvore;
        } else {
            $no = EncontraDuplaNegacao::exec($this->arvore, $noInsercao);
            $noBifur = EncontraNoBifurca::exec($this->arvore, $noInsercao);
            $noSemBifur = EncontraNoSemBifucacao::exec($this->arvore, $noInsercao);

            if (!is_null($noBifur)) {
                $qntdNegado = $noBifur->getValorNo()->getNegadoPredicado();
                $regra = $noBifur->getValorNo()->getTipoPredicado()->regra($qntdNegado);
                $passo = new PassoDerivacao([
                    'idNoDerivacao'  => $noBifur->getIdNo(),
                    'idsNoInsercoes' => [$noInsercao->getIdNo()],
                    'regra'          => $regra,
                ]);
            } elseif (!is_null($noSemBifur)) {
                $qntdNegado = $noSemBifur->getValorNo()->getNegadoPredicado();
                $regra = $noSemBifur->getValorNo()->getTipoPredicado()->regra($qntdNegado);
                $passo = new PassoDerivacao([
                    'idNoDerivacao'  => $noSemBifur->getIdNo(),
                    'idsNoInsercoes' => [$noInsercao->getIdNo()],
                    'regra'          => $regra,
                ]);
            } elseif (!is_null($no)) {
                $qntdNegado = $no->getValorNo()->getNegadoPredicado();
                $regra = $no->getValorNo()->getTipoPredicado()->regra($qntdNegado);
                $passo = new PassoDerivacao([
                    'idNoDerivacao'  => $no->getIdNo(),
                    'idsNoInsercoes' => [$noInsercao->getIdNo()],
                    'regra'          => $regra,
                ]);
            }

            if (isset($passo)) {
                $tentativa = $this->derivar($passo);

                if (!$tentativa->getSucesso()) {
                    return  $tentativa;
                }
                return $this->arvoreOtimizada();
            }
            return $this->arvore;
        }
    }
}
