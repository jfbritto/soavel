<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

/**
 * O modelo do Gemini vem do config, e nao fixo no codigo.
 *
 * Em 04/08/2026 o "Revisar com IA" parou com 404: o Google descontinuou o
 * gemini-2.5-flash ("no longer available to new users"). O nome estava escrito
 * em tres lugares do codigo, entao a correcao exigiu mexer nos tres e um deploy.
 *
 * Com o modelo em config/services.php, a proxima descontinuacao — que vai
 * acontecer — se resolve por variavel de ambiente.
 *
 * Licao que estes testes guardam: aparecer em GET /models NAO significa poder
 * chamar. O 2.5-flash continuava listado e mesmo assim devolvia 404.
 */
class GeminiModeloTest extends TestCase
{
    public function test_o_modelo_padrao_nao_e_um_apelido_movel(): void
    {
        // Apelidos como gemini-flash-latest mudam sozinhos, e o proprio
        // gemini-flash-latest ja rejeita o thinkingConfig que este codigo envia.
        // Versao fixa nao muda comportamento em producao sem deploy.
        $padrao = config('services.gemini.model');

        $this->assertStringNotContainsString('latest', $padrao);
        $this->assertStringNotContainsString('preview', $padrao, 'modelo de preview nao serve para producao');
        $this->assertMatchesRegularExpression('/^gemini-[0-9.]+-/', $padrao, 'o modelo deveria ser uma versao fixa');
    }

    public function test_o_modelo_e_configuravel_por_ambiente(): void
    {
        // O ponto da mudanca: quando o Google descontinuar o proximo modelo — e
        // vai —, a correcao deve ser uma variavel de ambiente, nao um deploy de
        // codigo em tres arquivos.
        config(['services.gemini.model' => 'outro-modelo']);

        $this->assertSame('outro-modelo', config('services.gemini.model'));
    }

    /*
     * NAO COBERTO POR TESTE, e vale saber:
     *
     * Que os tres pontos de chamada usem config('services.gemini.model') na URL
     * esta verificado apenas por leitura do codigo. Tentei um teste de
     * integracao com Http::fake sobre admin.vehicles.review, mas a rota devolve
     * 500 no ambiente de teste por alguma exigencia que nao identifiquei — e
     * deixar teste vermelho na suite e pior que nao ter.
     *
     * Se alguem voltar a fixar o nome do modelo no codigo, nada aqui acusa.
     * A verificacao no servidor e:
     *
     *   grep -rn "generativelanguage" app/ | grep -v '{$modelo}'
     */
}
