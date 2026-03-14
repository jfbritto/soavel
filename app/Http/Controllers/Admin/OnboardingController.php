<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    /**
     * Etapas do wizard: slug => config.
     */
    private const STEPS = [
        1 => [
            'slug'   => 'identidade',
            'title'  => 'Identidade da Loja',
            'icon'   => 'fas fa-store',
            'fields' => ['nome_sistema', 'slogan'],
            'files'  => ['logo_path', 'favicon_path'],
        ],
        2 => [
            'slug'   => 'empresa',
            'title'  => 'Dados da Empresa',
            'icon'   => 'fas fa-building',
            'fields' => ['razao_social', 'cnpj', 'cidade_estado', 'telefone_comercial', 'endereco_completo', 'horario_atendimento', 'descricao_empresa', 'maps_embed_url'],
            'files'  => [],
        ],
        3 => [
            'slug'   => 'contato',
            'title'  => 'Contato e Redes Sociais',
            'icon'   => 'fas fa-address-book',
            'fields' => ['whatsapp_number', 'instagram_url', 'facebook_url'],
            'files'  => [],
        ],
        4 => [
            'slug'   => 'site',
            'title'  => 'Personalização do Site',
            'icon'   => 'fas fa-paint-brush',
            'fields' => ['site_titulo_home', 'site_descricao_home', 'hero_titulo', 'stat_clientes', 'stat_anos', 'cor_primaria', 'site_layout'],
            'files'  => [],
        ],
    ];

    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $currentStep = $this->detectCurrentStep($settings);

        return redirect()->route('admin.onboarding.step', $currentStep);
    }

    public function step(int $step)
    {
        if (! isset(self::STEPS[$step])) {
            return redirect()->route('admin.onboarding.step', 1);
        }

        $settings  = Setting::all()->keyBy('key');
        $stepConfig = self::STEPS[$step];
        $totalSteps = count(self::STEPS);
        $allSteps   = self::STEPS;

        return view('admin.onboarding.step', compact('step', 'stepConfig', 'totalSteps', 'allSteps', 'settings'));
    }

    public function save(Request $request, int $step)
    {
        if (! isset(self::STEPS[$step])) {
            return redirect()->route('admin.onboarding.index');
        }

        $config = self::STEPS[$step];

        // Validações específicas por etapa
        $rules = [];
        if ($step === 1) {
            $rules['nome_sistema'] = 'required|string|max:100';
            $rules['logo_path']    = 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048';
            $rules['favicon_path'] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:512';
        }
        if ($step === 3) {
            $rules['whatsapp_number'] = 'required|string|regex:/^\d{10,13}$/';
        }

        $request->validate($rules);

        // Salvar campos de texto
        foreach ($config['fields'] as $key) {
            Setting::set($key, $request->input($key, ''), $config['slug']);
        }

        // Salvar arquivos (logo, favicon)
        foreach ($config['files'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $old = Setting::get($fileKey);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($fileKey)->store('settings', 'public');
                Setting::set($fileKey, $path, 'identidade');
            }
        }

        Setting::flushAll();

        // Próxima etapa ou finalizar
        $nextStep = $step + 1;
        if (isset(self::STEPS[$nextStep])) {
            return redirect()->route('admin.onboarding.step', $nextStep)
                ->with('success', 'Etapa salva com sucesso!');
        }

        return redirect()->route('admin.onboarding.complete');
    }

    public function complete()
    {
        $settings = Setting::all()->keyBy('key');
        $pending  = $this->getPendingItems($settings);
        $allSteps = self::STEPS;

        return view('admin.onboarding.complete', compact('pending', 'allSteps', 'settings'));
    }

    public function finish()
    {
        return redirect()->route('admin.dashboard')
            ->with('success', 'Configuração inicial concluída! Seu site está pronto.');
    }

    /**
     * Gera textos do onboarding com IA (Gemini).
     */
    public function aiGenerate(Request $request)
    {
        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return response()->json(['error' => 'Chave da API Gemini não configurada.'], 422);
        }

        $request->validate([
            'nome_loja'    => 'required|string|max:100',
            'cidade'       => 'nullable|string|max:100',
            'segmento'     => 'nullable|string|max:100',
        ]);

        $nome     = $request->input('nome_loja');
        $cidade   = $request->input('cidade', '');
        $segmento = $request->input('segmento', 'seminovos e usados');

        $cidadeCtx = $cidade ? " localizada em {$cidade}" : '';

        $prompt = "Você é um copywriter especialista em marketing automotivo. "
            . "Gere textos para o site de uma loja de veículos chamada \"{$nome}\"{$cidadeCtx}, "
            . "que trabalha com {$segmento}.\n\n"
            . "Gere os seguintes textos em português brasileiro, curtos e atrativos:\n\n"
            . "1. **slogan**: frase de efeito curta (máx 60 caracteres)\n"
            . "2. **descricao_empresa**: descrição da loja para o rodapé (máx 200 caracteres)\n"
            . "3. **hero_titulo**: título impactante para o banner principal do site (máx 50 caracteres)\n"
            . "4. **site_titulo_home**: título da página para SEO — formato: \"Palavra-chave | {$nome}\" (máx 60 caracteres)\n"
            . "5. **site_descricao_home**: meta description para Google (máx 155 caracteres, incluir cidade se disponível)\n"
            . "6. **horario_sugerido**: sugestão de horário de atendimento típico para loja de veículos\n\n"
            . "Responda APENAS com um JSON object com essas 6 chaves. Sem explicações, sem markdown, apenas o JSON.\n"
            . "Exemplo: {\"slogan\": \"...\", \"descricao_empresa\": \"...\", ...}";

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature'   => 0.7,
                    'maxOutputTokens' => 2048,
                    'thinkingConfig' => ['thinkingBudget' => 0],
                ],
            ]);

            if (! $response->successful()) {
                return response()->json(['error' => 'Erro na API: ' . $response->status()], 500);
            }

            $parts = $response->json('candidates.0.content.parts', []);
            $text = '';
            foreach ($parts as $part) {
                if (! ($part['thought'] ?? false)) {
                    $text .= ($part['text'] ?? '');
                }
            }

            // Extrai o JSON da resposta
            $start = strpos($text, '{');
            $end   = strrpos($text, '}');

            if ($start === false || $end === false || $end <= $start) {
                return response()->json(['error' => 'Não foi possível interpretar a resposta da IA.'], 422);
            }

            $jsonStr = substr($text, $start, $end - $start + 1);
            $data    = json_decode($jsonStr, true);

            if (! is_array($data)) {
                return response()->json(['error' => 'Resposta inválida da IA.'], 422);
            }

            return response()->json(['suggestions' => $data]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar IA: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Detecta a primeira etapa incompleta.
     */
    private function detectCurrentStep($settings): int
    {
        // Se nome_sistema vazio, começa do 1
        if (empty($settings['nome_sistema']->value ?? null)) {
            return 1;
        }
        // Se whatsapp vazio, vai pro 3
        if (empty($settings['whatsapp_number']->value ?? null)) {
            return 3;
        }

        return 1;
    }

    /**
     * Lista itens pendentes (recomendados mas não preenchidos).
     */
    private function getPendingItems($settings): array
    {
        $recommended = [
            'nome_sistema'      => 'Nome do Sistema',
            'whatsapp_number'   => 'WhatsApp',
            'logo_path'         => 'Logo',
            'favicon_path'      => 'Favicon',
            'descricao_empresa' => 'Descrição da Empresa',
            'cidade_estado'     => 'Cidade / Estado',
            'telefone_comercial'=> 'Telefone Comercial',
            'endereco_completo' => 'Endereço Completo',
            'cor_primaria'      => 'Cor da Marca',
            'hero_titulo'       => 'Título do Banner',
            'site_titulo_home'  => 'Título SEO',
            'site_descricao_home' => 'Descrição SEO',
        ];

        $pending = [];
        foreach ($recommended as $key => $label) {
            if (empty($settings[$key]->value ?? null)) {
                $pending[$key] = $label;
            }
        }

        return $pending;
    }
}
