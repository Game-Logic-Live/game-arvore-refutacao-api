<?php

namespace App\Core\Generators;

use App\Core\Common\Models\Formula\Formula;
use App\Core\Common\Models\Formula\Predicado;
use App\Core\Common\Models\Formula\Premissa;
use App\Core\Common\Models\PrintTree\Aresta;
use App\Core\Common\Models\PrintTree\Arvore;
use App\Core\Common\Models\PrintTree\Linha;
use App\Core\Common\Models\PrintTree\No as VizualizadoresNo;
use App\Core\Common\Models\Steps\PassoInicializacao;
use App\Core\Common\Models\Tree\No as ProcessadoresNo;
use App\Core\Common\Models\Tree\OpcaoInicializacao;
use App\Core\Helpers\Buscadores\EncontraNoMaisProfundo;
use App\Core\Helpers\Buscadores\EncontraTodosNosFolha;
use App\Http\Controllers\Controller;
use SimpleXMLElement;

class Visualizador extends Controller
{
    public const AREA_LINHA = 90;
    public const DISTANCIA_Y_ENTRE_NOS = 80;
    public const AREA_LINHA_ICONE_TICAGEM = 54;
    public const ALTURA_NO = 40;
    private GeradorFormula $geradorFormula;
    private bool $showLines = true;

    public function __construct()
    {
        $this->geradorFormula = new GeradorFormula();
    }

    /**
     * @param ProcessadoresNo|null $arvore
     * @param SimpleXMLElement     $xml
     * @param float                $width
     * @param bool                 $ticar
     * @param bool                 $fechar
     * @param Formula              $formula
     * @param bool                 $showLines
     */
    public function gerarImpressaoArvore(?ProcessadoresNo $arvore, Formula $formula, float $width, bool $ticar = false, bool $fechar = false, bool $showLines = true)
    {
        $this->showLines = $showLines;
        $larguraMininoCanvas = $this->larguraMinimaCanvas($formula);
        $alturaMininoCanvas = $this->alturaMinimaCanvas($formula);

        if ($width < $larguraMininoCanvas) {
            $width = $larguraMininoCanvas;
        }

        if (is_null($arvore)) {
            return new Arvore([
                'nos'       => [],
                'arestas'   => [],
                'linhas'    => [],
                'width'     => $width + ($this->showLines ? self::AREA_LINHA : 0),
                'height'    => $larguraMininoCanvas,
            ]);
        }

        $listaNo = $this->imprimirNos($arvore, $width, $width / 2, 0, $ticar, $fechar);
        $listaAresta = $this->imprimirArestas($listaNo);
        $linhas = $this->imprimirLinhas($listaNo);
        return new Arvore([
            'nos'       => $listaNo,
            'arestas'   => $listaAresta,
            'linhas'    => $this->showLines ? $linhas : [],
            'width'     => $width + ($this->showLines ? self::AREA_LINHA : 0),
            'height'    => $alturaMininoCanvas,
        ]);
    }

    /**
     * @param  Formula              $formula
     * @param  PassoInicializacao[] $passosExcutado
     * @return OpcaoInicializacao[]
     */
    public function gerarOpcoesInicializacao(Formula $formula, array $passosExcutado): array
    {
        $opcoes = [];

        $premissas = $formula->getPremissas();
        $conclusao = $formula->getConclusao();

        foreach ($passosExcutado as $passo) {
            $executado = array_filter($premissas, fn (Premissa $p) => $p->getId() == $passo->getIdNo());

            if ($executado) {
                unset($premissas[key($executado)]);
            } elseif ($conclusao->getId() == $passo->getIdNo()) {
                unset($conclusao);
            }
        }

        foreach ($premissas as $premissa) {
            $str = $this->geradorFormula->stringArg($premissa->getValorObjPremissa()) ;
            array_push($opcoes, new OpcaoInicializacao([
                'id'       => $premissa->getId(),
                'texto'    => trim($str),
            ]));
        }

        if (isset($conclusao)) {
            $str = $this->geradorFormula->stringArg($conclusao->getValorObjConclusao()) ;
            array_push($opcoes, new OpcaoInicializacao([
                'id'       => $conclusao->getId(),
                'texto'    => $str,
            ]));
        }

        return $opcoes;
    }

    /**
     * @param  Formula $formula
     * @return float
     */
    protected function larguraMinimaCanvas(Formula $formula): float
    {
        $maiorLargura = 0;

        $premissas = $formula->getPremissas();

        foreach ($premissas as $premissas) {
            $tamanho = $this->calcularLarguraPredicado($premissas->getValorObjPremissa());
            $maiorLargura = $maiorLargura < $tamanho ? $tamanho : $maiorLargura;
        }
        $conclusao = $formula->getConclusao();
        $predicado = $conclusao->getValorObjConclusao();
        $tamanho = $this->calcularLarguraPredicado($predicado);
        $maiorLargura = $maiorLargura < $tamanho ? $tamanho : $maiorLargura;

        $gerador = new GeradorAutomatico();
        $gerador->inicializar($formula);
        $gerador->piorArvore();
        $arvore = $gerador->getArvore();
        $nosFolhas = EncontraTodosNosFolha::exec($arvore);

        return ($maiorLargura + self::AREA_LINHA_ICONE_TICAGEM) * count($nosFolhas);
    }

        /**
         * @param  Formula $formula
         * @return float
         */
    protected function alturaMinimaCanvas(Formula $formula): float
    {
        $gerador = new GeradorAutomatico();
        $gerador->inicializar($formula);
        $gerador->piorArvore();
        $arvore = $gerador->getArvore();

        $noMaisProfundo = EncontraNoMaisProfundo::exec($arvore);
        $ultimaLinha = $noMaisProfundo[0]->getLinhaNo();

        return  ((self::DISTANCIA_Y_ENTRE_NOS + 5) * $ultimaLinha) + self::DISTANCIA_Y_ENTRE_NOS;
    }

    /**
     * @param  VizualizadoresNo[] $nos
     * @return Aresta[]
     */
    protected function imprimirArestas(array $nos): array
    {
        $listaAresta = [];

        for ($i = 1; $i < count($nos); ++$i) {
            if ($nos[$i - 1]->getPosY() >= $nos[$i]->getPosY()) {
                for ($e = $i - 1 ; $e > 0; --$e) {
                    if ($nos[$e]->getPosY() < $nos[$i]->getPosY()) {
                        array_push(
                            $listaAresta,
                            new Aresta([
                                'linhaX1' => $nos[$e]->getPosX(),
                                'linhaY1' => $nos[$e]->getPosY() + 27,
                                'linhaX2' => $nos[$i]->getPosX(),
                                'linhaY2' => $nos[$i]->getPosY() - 27,
                            ])
                        );
                        break;
                    }
                }
            } else {
                array_push($listaAresta, [
                    'linhaX1' => $nos[$i - 1]->getPosX(),
                    'linhaY1' => $nos[$i - 1]->getPosY() + 27,
                    'linhaX2' => $nos[$i]->getPosX(),
                    'linhaY2' => $nos[$i]->getPosY() - 27,
                ]);
            }
        }
        return $listaAresta;
    }

    /**
     * @param  ProcessadoresNo    $arvore
     * @param  float              $width
     * @param  float              $posX
     * @param  float              $posY
     * @param  bool               $ticar
     * @param  bool               $fechar
     * @param  ProcessadoresNo[]  $listaNosVisualizadores -> Usado para acesso recursivo
     * @return VizualizadoresNo[]
     */
    protected function imprimirNos(ProcessadoresNo $arvore, float $width, float $posX, float $posY, bool $ticar, bool $fechar, array $listaNosVisualizadores = []): array
    {
        $posYFilho = $posY + self::DISTANCIA_Y_ENTRE_NOS;
        $tmh = $this->calcularLarguraPredicado($arvore->getValorNo());

        $utilizado = $ticar == false ? $arvore->isTicado() : $arvore->isUtilizado();
        $fechado = $fechar == false ? $arvore->isFechamento() : $arvore->isFechado();

        $no = new VizualizadoresNo([
            'str'                  => $this->geradorFormula->stringArg($arvore->getValorNo()),
            'idNo'                 => $arvore->getIdNo(),
            'linha'                => $arvore->getLinhaNo(),
            'noFolha'              => $arvore->isNoFolha(),
            'posX'                 => $posX + ($this->showLines ? self::AREA_LINHA : 0),
            'posY'                 => $posYFilho,
            'width'                => $tmh,
            'height'               => self::ALTURA_NO,
            'posXno'               => $posX - ($tmh / 2) + ($this->showLines ? self::AREA_LINHA : 0),
            'linhaDerivacao'       => $arvore->getLinhaDerivacao(),
            'posXlinhaDerivacao'   => $posX + ($tmh / 2) + ($this->showLines ? self::AREA_LINHA : 0),
            'utilizado'            => $utilizado,
            'fechado'              => $fechado,
            'linhaContradicao'     => $arvore->getLinhaContradicao(),
            'fill'                 => 'url(#grad1)',
            'strokeWidth'          => 2,
            'strokeColor'          => '#C0C0C0',
        ]);
        array_push($listaNosVisualizadores, $no);

        if (!is_null($arvore->getFilhoEsquerdaNo())) {
            $areaFilho = $width / 2 ;
            $posicaoAreaFilho = $areaFilho / 2;
            $posXFilho = $posX - $posicaoAreaFilho ;
            $listaNosVisualizadores = $this->imprimirNos($arvore->getFilhoEsquerdaNo(), $areaFilho, $posXFilho, $posYFilho, $ticar, $fechar, $listaNosVisualizadores);
        }

        if (!is_null($arvore->getFilhoCentroNo())) {
            $listaNosVisualizadores = $this->imprimirNos($arvore->getFilhoCentroNo(), $width, $posX, $posYFilho, $ticar, $fechar, $listaNosVisualizadores);
        }

        if (!is_null($arvore->getFilhoDireitaNo())) {
            $areaFilho = $width / 2 ;
            $posicaoAreaFilho = $areaFilho / 2;
            $posXFilho = $posX + $posicaoAreaFilho ;
            $listaNosVisualizadores = $this->imprimirNos($arvore->getFilhoDireitaNo(), $areaFilho, $posXFilho, $posYFilho, $ticar, $fechar, $listaNosVisualizadores);
        }
        return $listaNosVisualizadores;
    }

    /**
     * @param  VizualizadoresNo[] $nos
     * @return Linha[]
     */
    protected function imprimirLinhas(array $nos)
    {
        $listaLinhas = [];

        foreach ($nos as $no) {
            $linha = array_filter($listaLinhas, fn (Linha $l) => $l->getNumero() == $no->getLinha()) ;

            if (empty($linha)) {
                array_push(
                    $listaLinhas,
                    new Linha([
                        'texto'  => 'Linha ' . $no->getLinha(),
                        'numero' => $no->getLinha(),
                        'posX'   => 5,
                        'posY'   => $no->getPosY() + 5,
                    ])
                );
            }
        }
        return $listaLinhas;
    }

    protected function calcularLarguraPredicado(Predicado $predicado)
    {
        $str = $this->geradorFormula->stringArg($predicado);
        return strlen($str) <= 4 ? 40 : (strlen($str) >= 18 ? strlen($str) * 6 : strlen($str) * 8.5);
    }
}
