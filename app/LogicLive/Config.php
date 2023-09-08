<?php

namespace App\LogicLive;

class Config
{
    private $ativar;
    private $token;
    private $urlAPI;
    private $urlGame;
    private $urlExercicioValidacao = 'exercicio/validacao/';

    public function __construct()
    {
        if (env('LOGIC_LIVE_ENV') == 'PROD') {
            $this->ativar = env('LOGIC_LIVE_REGISTER_PROD') == true;
            $this->token = env('LOGIC_LIVE_TOKEN_PROD');
            $this->urlAPI = env('LOGIC_LIVE_URL_API_PROD');
            $this->urlGame = env('LOGIC_LIVE_URL_GAME_PROD');
        } elseif (env('LOGIC_LIVE_ENV') == 'DEV') {
            $this->ativar = env('LOGIC_LIVE_REGISTER_DEV') == true;
            $this->token = env('LOGIC_LIVE_TOKEN_DEV');
            $this->urlAPI = env('LOGIC_LIVE_URL_API_DEV');
            $this->urlGame = env('LOGIC_LIVE_URL_GAME_DEV');
        }
    }

    public function ativo()
    {
        return $this->ativar;
    }

    public function token()
    {
        return $this->token;
    }

    public function urlAPI(?string $path): string
    {
        if (is_null($path)) {
            return $this->urlAPI;
        }
        return $this->urlAPI . '/' . $path;
    }

    public function urlGame(?string $path): string
    {
        if (is_null($path)) {
            return $this->urlAPI;
        }
        return $this->urlGame . '/' . $path;
    }
}
