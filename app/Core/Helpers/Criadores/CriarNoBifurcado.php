<?php

namespace App\Core\Helpers\Criadores;

use App\Core\Common\Models\Tree\No;
use App\Core\Helpers\Buscadores\EncontraContradicao;

class CriarNoBifurcado
{
    /**
     * Responsavel por inserir novos NOS na esquerda e direira do NO de insercao e
     * tambem aplicar a validação de contradição caso exista
     * @param No             $noInsercao
     * @param No             $arvore
     * @param RegrasResponse $filhos
     * @param int            $linhaDerivado
     * @param Array<int>     $idsNo
     */
    public static function exec(No &$noInsercao, No &$arvore, $filhos, int $linhaDerivado, array $idsNo): void
    {
        $noInsercao->setFilhoEsquerdaNo(new No($idsNo[0], $filhos->getEsquerda()[0], null, null, null, $noInsercao->getLinhaNo() + 1, null, $linhaDerivado, false, false));

        $contradicao = EncontraContradicao::exec($arvore, $noInsercao->getFilhoEsquerdaNo());

        if (!is_null($contradicao)) {
            $noInsercao->getFilhoEsquerdaNo()->fecharRamo($contradicao->getLinhaNo());
        }

        $noInsercao->setFilhoDireitaNo(new No($idsNo[1], $filhos->getdireita()[0], null, null, null, $noInsercao->getLinhaNo() + 1, null, $linhaDerivado, false, false));
        $contradicao = EncontraContradicao::exec($arvore, $noInsercao->getFilhoDireitaNo());

        if (!is_null($contradicao)) {
            $noInsercao->getFilhoDireitaNo()->fecharRamo($contradicao->getLinhaNo());
        }
    }
}
