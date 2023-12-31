<?php

namespace App\Core\Common\Models\Tree;

use App\Core\Common\Models\Formula\Predicado;

class No
{
    /** Indentificador unico que deve ser atribuido na criacao da arvore */
    protected int $id;

    /** Conteudo do "No" */
    protected Predicado $valor;

    /** Ramo descendo no esquerda (aplicação da regra) */
    protected ?No $filho_esquerda;

    /** Ramo descendo no centro (separação das premissas) */
    protected ?No $filho_centro;

    /** Ramo descendo no direita (aplicação da regra) */
    protected ?No $filho_direita;

    /** Linha em que esta o No */
    protected int $linha;

    /** A linha do nó que encontrou sua contradição */
    protected ?int $linhaContradicao;

    /** A linha do nó no qual foi derivado */
    protected ?int $linhaDerivacao;

    /** Sê o No já foi utilizado para derivação */
    protected bool $utilizada;

    /** Indica sê o nó está fechado */
    protected bool $fechado;

    /** Verifica sê é um NÓ folha, essa verificação e feita automatacamente */
    protected bool $noFolha;

    /** informa sê o usuario já informou o fechamento */
    protected bool $fechamento;

    /** informa sê o usuario já informou a ticagem do nó */
    protected bool $ticar;

    public function __construct(
        int $id,
        object $valor,
        ?No $filho_esquerda = null,
        ?No $filho_centro = null,
        ?No $filho_direita = null,
        int $linha,
        ?int $linhaContradicao = null,
        ?int $linhaDerivacao = null,
        bool $utilizada,
        bool $fechado
    ) {
        $this->id = $id;
        $this->valor = $valor;
        $this->filho_direita = $filho_direita;
        $this->filho_esquerda = $filho_esquerda;
        $this->filho_centro = $filho_centro;
        $this->linha = $linha;
        $this->linhaContradicao = $linhaContradicao;
        $this->linhaDerivacao = $linhaDerivacao;
        $this->utilizada = $utilizada;
        $this->fechado = $fechado;
        $this->fechamento = false;
        $this->ticar = false;

        if ($filho_direita == null && $filho_centro == null && $filho_direita == null) {
            $this->noFolha = true;
        } else {
            $this->noFolha = false;
        }
    }

    /**
     * @return int
     */
    public function getIdNo(): int
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     * @return void
     */
    public function setIdNo(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Predicado
     */
    public function getValorNo(): Predicado
    {
        return $this->valor;
    }

    /**
     * @param  Predicado $valor
     * @return void
     */
    public function setValorNo(Predicado $valor): void
    {
        $this->valor = $valor;
    }

    /**
     * @return No|null
     */
    public function &getFilhoCentroNo(): ?No
    {
        return $this->filho_centro;
    }

    /**
     * @param  No   $centro
     * @return void
     */
    public function setFilhoCentroNo(No $centro): void
    {
        $this->filho_centro = $centro;
        $this->noFolha = false;
    }

    /**
     * @return void
     */
    public function removeFilhoCentroNo(): void
    {
        $this->filho_centro = null;

        if ($this->filho_direita == null && $this->filho_centro == null && $this->filho_direita == null) {
            $this->noFolha = true;
        } else {
            $this->noFolha = false;
        }
    }

    /**
     * @return No|null
     */
    public function &getFilhoDireitaNo(): ?No
    {
        return $this->filho_direita;
    }

    /**
     * @param  No   $direita
     * @param  No   $no
     * @return void
     */
    public function setFilhoDireitaNo(No $no): void
    {
        $this->filho_direita = $no;
        $this->noFolha = false;
    }

    /**
     * @return No|null
     */
    public function &getFilhoEsquerdaNo(): ?No
    {
        return $this->filho_esquerda;
    }

    /**
     * @param  No   $no
     * @return void
     */
    public function setFilhoEsquerdaNo(No $no): void
    {
        $this->filho_esquerda = $no;
        $this->noFolha = false;
    }

    /**
     * @return int
     */
    public function getLinhaNo(): int
    {
        return $this->linha;
    }

    /**
     * @param  int  $linha
     * @return void
     */
    public function setLinhaNo(int $linha): void
    {
        $this->linha = $linha;
    }

    /**
     * @param  int  $linha
     * @return void
     */
    public function fecharRamo(int $linha): void
    {
        $this->fechado = true;
        $this->linhaContradicao = $linha;
    }

    /**
     * @param  int|null $linha
     * @return void
     */
    public function setLinhaDerivacao(?int $linha): void
    {
        $this->linhaDerivacao = $linha;
    }

    /**
     * @return int|null
     */
    public function getLinhaDerivacao(): ?int
    {
        return $this->linhaDerivacao;
    }

    /**
     * @return int|null
     */
    public function getLinhaContradicao(): ?int
    {
        return $this->linhaContradicao;
    }

    /**
     * @return bool
     */
    public function isFechado(): bool
    {
        return $this->fechado;
    }

    /**
     * @return bool
     */
    public function isUtilizado(): bool
    {
        return $this->utilizada;
    }

    /**
     * @param  bool $valor
     * @return void
     */
    public function utilizado(bool $valor): void
    {
        $this->utilizada = $valor;
    }

    /**
     * @return bool
     */
    public function isNoFolha(): bool
    {
        return $this->noFolha;
    }

    /**
     * @return string
     */
    public function getStringNo(): string
    {
        return $this->valor->getValorPredicado();
    }

    /**
     * @return void
     */
    public function ticarNo(): void
    {
        $this->ticar = true;
    }

    /**
     * @return bool
     */
    public function isTicado(): bool
    {
        return $this->ticar;
    }

    /**
     * @return void
     */
    public function fechamentoNo(): void
    {
        $this->fechamento = true;
    }

    /**
     * @return bool
     */
    public function isFechamento(): bool
    {
        return $this->fechamento;
    }
}
