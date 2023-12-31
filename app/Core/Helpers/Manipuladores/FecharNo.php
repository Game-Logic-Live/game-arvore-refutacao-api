<?php

namespace App\Core\Helpers\Manipuladores;

use App\Core\Common\Models\Attempts\TentativaFechamento;
use App\Core\Common\Models\Steps\PassoFechamento;
use App\Core\Common\Models\Tree\No;
use App\Core\Helpers\Buscadores\EncontraNoPeloId;
use App\Core\Helpers\Validadores\IsDecendente;

class FecharNo
{
    /**
     *
     * @param No              $arvore
     * @param PassoFechamento $passo
     */
    public static function exec(No &$arvore, PassoFechamento $passo): TentativaFechamento
    {
        $noContradicao = EncontraNoPeloId::exec($arvore, $passo->getIdNoContraditorio());
        $noFolha = EncontraNoPeloId::exec($arvore, $passo->getIdNoFolha());

        if (IsDecendente::exec($noContradicao, $noFolha)) {
            if ($noContradicao->getValorNo()->getValorPredicado() == $noFolha->getValorNo()->getValorPredicado()) {
                $negacaoContradicao = $noContradicao->getValorNo()->getNegadoPredicado();
                $negacaoFolha = $noFolha->getValorNo()->getNegadoPredicado();

                if ($negacaoContradicao == 1 and $negacaoFolha == 0) {
                    if ($noFolha->isFechamento()) {
                        return new TentativaFechamento([
                            'sucesso'  => false,
                            'mensagem' => 'O ramo já foi fechado',
                        ]);
                    }
                    $noFolha->fechamentoNo();
                    return new TentativaFechamento([
                        'sucesso'  => true,
                        'mensagem' => 'Fechado com sucesso',
                        'arvore'   => $arvore,
                        'passos'   => [$passo],
                    ]);
                } elseif ($negacaoContradicao == 0 and $negacaoFolha == 1) {
                    if ($noFolha->isFechamento()) {
                        return new TentativaFechamento([
                            'sucesso'  => false,
                            'mensagem' => 'O ramo já foi fechado',
                        ]);
                    }
                    $noFolha->fechamentoNo();
                    return new TentativaFechamento([
                        'sucesso'  => true,
                        'mensagem' => 'Fechado com sucesso',
                        'arvore'   => $arvore,
                        'passos'   => [$passo],
                    ]);
                } else {
                    return new TentativaFechamento([
                        'sucesso'  => false,
                        'mensagem' => 'Os argumentos iguais mas não contraditórios',
                    ]);
                }
            } else {
                return new TentativaFechamento([
                    'sucesso'  => false,
                    'mensagem' => 'Os argumentos não são iguais',
                ]);
            }
        } else {
            return new TentativaFechamento([
                'sucesso'  => false,
                'mensagem' => 'O nó não pertence ao mesmo ramo',
            ]);
        }
    }
}
