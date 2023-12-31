<?php

namespace App\Core\Common\Models\Steps;

use App\Core\Common\Serialization\Serializa;

class PassoInicializacao extends Serializa
{
    protected string $idNo;
    protected bool $negacao;

    /**
     * @return string
     */
    public function getIdNo(): string
    {
        return $this->idNo;
    }

    /**
     * @param  string $idNo
     * @return void
     */
    public function setIdNo(string $idNo): void
    {
        $this->idNo = $idNo;
    }

    /**
     * @return bool
     */
    public function getNegacao(): bool
    {
        return $this->negacao;
    }

    /**
     * @param  bool $negacao
     * @return void
     */
    public function setNegacao(bool $negacao): void
    {
        $this->negacao = $negacao;
    }
}
