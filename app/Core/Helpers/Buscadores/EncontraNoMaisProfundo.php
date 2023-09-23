<?php

namespace App\Core\Helpers\Buscadores;

use App\Core\Common\Models\Tree\No;

class EncontraNoMaisProfundo
{
    /**
     * Realiza uma busca na arvore por todos os NOS folhas
     * e retorna os mais profundos
     * @param  No    $arvore
     * @param  array $listaDeNo -> Utilizado para busca recursiva
     * @return No[]
     */
    public static function exec(No &$arvore, array $listaDeNo = []): array
    {
        $ramoCentro = $arvore->getFilhoCentroNo();
        $ramoEsquerdo = $arvore->getFilhoEsquerdaNo();
        $ramoDireito = $arvore->getFilhoDireitaNo();

        if (is_null($ramoDireito) and is_null($ramoEsquerdo) and is_null($ramoCentro)) {
            array_push($listaDeNo, $arvore);
            $listaDeNo = array_reduce(
                $listaDeNo,
                function (array $carry, No $no) {
                    if (empty($carry)) {
                        array_push($carry, $no);
                    } elseif ($carry[0]->getLinhaNo() < $no->getLinhaNo()) {
                        $carry = [$no];
                    } elseif ($carry[0]->getLinhaNo() == $no->getLinhaNo()) {
                        array_push($carry, $no);
                    }
                    return $carry;
                },
                []
            );
            return  $listaDeNo;
        } else {
            if (!is_null($ramoCentro)) {
                $listaDeNo = self::exec($ramoCentro, $listaDeNo);
            }

            if (!is_null($ramoEsquerdo)) {
                $listaDeNo = self::exec($ramoEsquerdo, $listaDeNo);
            }

            if (!is_null($ramoDireito)) {
                $listaDeNo = self::exec($ramoDireito, $listaDeNo);
            }
            return $listaDeNo;
        }
    }
}
