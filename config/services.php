<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),

        // O modelo fica aqui, e nao fixo no codigo, porque o Google descontinua
        // versoes: em 04/08/2026 o gemini-2.5-flash passou a responder 404 com
        // "no longer available to new users" e o "Revisar com IA" parou.
        //
        // Versao FIXA, e nao um apelido como gemini-flash-latest: apelido muda
        // sozinho, e o proprio gemini-flash-latest ja rejeita o thinkingConfig
        // que este codigo envia. Versao fixa nao muda comportamento em producao
        // sem deploy, e o env permite trocar sem alterar codigo se voltar a cair.
        //
        // Ao trocar, testar CHAMANDO o modelo: aparecer em GET /models nao
        // significa poder usar. O 2.5-flash aparecia na lista e dava 404.
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
    ],

    'master' => [
        'token' => env('MASTER_API_TOKEN'),
        'url' => env('MASTER_URL', 'http://localhost:8002'),
    ],

];
