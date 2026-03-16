<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVehicleRequest;
use App\Http\Requests\Admin\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleFeature;
use Database\Seeders\VehicleFeatureSeeder;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::withCount(['photos', 'sales', 'partners'])
            ->with('principalPhoto');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('marca', 'like', "%{$s}%")
                  ->orWhere('modelo', 'like', "%{$s}%")
                  ->orWhere('versao', 'like', "%{$s}%")
                  ->orWhere('placa', 'like', "%{$s}%");
            });
        }

        $vehicles = $query->latest()->paginate(20)->withQueryString();
        $marcas   = Vehicle::distinct()->orderBy('marca')->pluck('marca');

        return view('admin.vehicles.index', compact('vehicles', 'marcas'));
    }

    public function create()
    {
        $featuresByCategory = VehicleFeatureSeeder::$featuresByCategory;
        return view('admin.vehicles.create', compact('featuresByCategory'));
    }

    public function store(StoreVehicleRequest $request)
    {
        $data           = $request->validated();
        $data['destaque'] = $request->boolean('destaque');
        $data['slug']   = Vehicle::generateSlug($data['marca'], $data['modelo'], $data['ano_modelo']);

        $features = $data['features'] ?? [];
        unset($data['features']);

        $vehicle = Vehicle::create($data);

        foreach ($features as $feature) {
            VehicleFeature::create(['vehicle_id' => $vehicle->id, 'feature' => $feature]);
        }

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Veículo cadastrado com sucesso! Adicione as fotos abaixo.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['photos', 'features', 'sales.customer', 'leads', 'partners', 'documents', 'vendaOrigem.vehicle.principalPhoto', 'vendaOrigem.customer']);
        $expenses    = $vehicle->expenses()->latest('data')->get();
        $allPartners = \App\Models\Partner::orderBy('nome')->get(['id', 'nome']);
        return view('admin.vehicles.show', compact('vehicle', 'expenses', 'allPartners'));
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicle->load('features');
        $featuresByCategory = VehicleFeatureSeeder::$featuresByCategory;
        $currentFeatures    = $vehicle->features->pluck('feature')->toArray();

        return view('admin.vehicles.edit', compact('vehicle', 'featuresByCategory', 'currentFeatures'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $data             = $request->validated();
        $data['destaque'] = $request->boolean('destaque');

        $features = $data['features'] ?? [];
        unset($data['features']);

        $vehicle->update($data);

        // Resync features
        $vehicle->features()->delete();
        foreach ($features as $feature) {
            VehicleFeature::create(['vehicle_id' => $vehicle->id, 'feature' => $feature]);
        }

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Veículo atualizado com sucesso!');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->sales()->exists()) {
            return back()->with('error', 'Não é possível excluir um veículo com vendas registradas.');
        }

        // Photos are deleted via cascade in DB; also remove from storage
        foreach ($vehicle->photos as $photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->path);
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Veículo excluído com sucesso.');
    }

    public function toggleDestaque(Vehicle $vehicle)
    {
        $vehicle->update(['destaque' => !$vehicle->destaque]);

        return response()->json([
            'success'  => true,
            'destaque' => $vehicle->destaque,
        ]);
    }

    public function suggestFeatures(Request $request)
    {
        $request->validate([
            'marca'  => 'required|string',
            'modelo' => 'required|string',
            'ano'    => 'required|string',
        ]);

        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            return response()->json(['error' => 'Chave da API Gemini não configurada.'], 422);
        }

        $allFeatures = VehicleFeatureSeeder::allFeatures();
        $featureList = implode(', ', $allFeatures);

        // Dados extras do formulário para contexto
        $versao      = $request->input('versao', '');
        $transmissao = $request->input('transmissao', '');
        $combustivel = $request->input('combustivel', '');
        $motorizacao = $request->input('motorizacao', '');
        $categoria   = $request->input('categoria', '');
        $portas      = $request->input('portas', '');

        $contexto = "Dados do veículo cadastrado:\n"
            . "- Marca: {$request->marca}\n"
            . "- Modelo: {$request->modelo}\n"
            . "- Versão/Acabamento: " . ($versao ?: 'não informada') . "\n"
            . "- Ano: {$request->ano}\n"
            . "- Transmissão: " . ($transmissao ?: 'não informada') . "\n"
            . "- Combustível: " . ($combustivel ?: 'não informado') . "\n"
            . "- Motorização: " . ($motorizacao ?: 'não informada') . "\n"
            . "- Categoria: " . ($categoria ?: 'não informada') . "\n"
            . "- Portas: " . ($portas ?: 'não informado') . "\n";

        $prompt = "Você é um especialista em veículos do mercado brasileiro. "
            . "Analise os dados do veículo abaixo e marque APENAS os opcionais que este veículo REALMENTE possui de fábrica.\n\n"
            . $contexto . "\n"
            . "REGRAS IMPORTANTES:\n"
            . "- Se a transmissão é Manual, NÃO inclua 'Câmbio automático' nem 'Câmbio CVT'\n"
            . "- Se a transmissão é Automático, inclua 'Câmbio automático' (não CVT, a menos que seja CVT)\n"
            . "- Se o veículo não tem tração 4x4, NÃO inclua 'Tração 4x4'\n"
            . "- Considere a versão/acabamento para saber o nível de equipamentos\n"
            . "- Versões básicas têm menos opcionais que versões top de linha\n"
            . "- Seja conservador: na dúvida, NÃO inclua o opcional\n\n"
            . "Lista de opcionais possíveis: {$featureList}\n\n"
            . "Responda APENAS com um JSON array contendo os nomes exatos dos opcionais. "
            . "Exemplo: [\"Ar-condicionado\", \"ABS\"]\n"
            . "Sem explicações, apenas o JSON array.";

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 8192,
                    'thinkingConfig' => [
                        'thinkingBudget' => 0,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Erro na API Gemini: ' . $response->status()], 500);
            }

            // Concatena todos os parts (Gemini 2.5 pode ter thinking + resposta)
            $parts = $response->json('candidates.0.content.parts', []);
            $text = '';
            foreach ($parts as $part) {
                if (!($part['thought'] ?? false)) {
                    $text .= ($part['text'] ?? '');
                }
            }

            // Remove tudo que não seja o JSON array
            $start = strpos($text, '[');
            $end   = strrpos($text, ']');

            if ($start === false || $end === false || $end <= $start) {
                \Log::error('Gemini response parsing failed', ['text' => substr($text, 0, 500)]);
                return response()->json(['error' => 'Não foi possível interpretar a resposta da IA.'], 422);
            }

            $jsonStr   = substr($text, $start, $end - $start + 1);
            $suggested = json_decode($jsonStr, true);

            if (!is_array($suggested)) {
                \Log::error('Gemini JSON decode failed', ['json' => substr($jsonStr, 0, 500)]);
                return response()->json(['error' => 'Resposta inválida da IA.'], 422);
            }

            // Filtra apenas features que existem na lista
            $valid = array_values(array_intersect($suggested, $allFeatures));

            return response()->json(['features' => $valid]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar IA: ' . $e->getMessage()], 500);
        }
    }

    public function reviewVehicle(Request $request)
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            return response()->json(['error' => 'Chave da API Gemini não configurada.'], 422);
        }

        $data = $request->validate([
            'marca'           => 'required|string',
            'modelo'          => 'required|string',
            'versao'          => 'nullable|string',
            'ano_fabricacao'  => 'required|string',
            'ano_modelo'      => 'required|string',
            'km'              => 'nullable|string',
            'cor'             => 'nullable|string',
            'combustivel'     => 'nullable|string',
            'transmissao'     => 'nullable|string',
            'motorizacao'     => 'nullable|string',
            'portas'          => 'nullable|string',
            'categoria'       => 'nullable|string',
            'preco'           => 'nullable|string',
            'preco_compra'    => 'nullable|string',
            'descricao'       => 'nullable|string',
        ]);

        $prompt = "Você é um consultor especialista em veículos do mercado brasileiro. "
            . "Sua tarefa é revisar e CORRIGIR o cadastro de um veículo seminovo.\n\n"
            . "REGRAS OBRIGATÓRIAS DE SEPARAÇÃO DOS CAMPOS:\n"
            . "- MARCA: apenas o nome do fabricante, com grafia oficial. Ex: Volkswagen (não VolksWagen), Chevrolet (não chevrolet), Hyundai, Toyota, Fiat, Ford, Honda, Jeep, Renault, Nissan, Mitsubishi, Peugeot, Citroën, BMW, Mercedes-Benz, Audi\n"
            . "- MODELO: apenas o nome do modelo, sem versão/motorização. Ex: Polo, Onix, HB20, Compass, Strada, Tracker, T-Cross, Creta, Kicks, Corolla, Civic, Hilux, S10\n"
            . "- VERSÃO: tudo que não é marca nem modelo — motorização, acabamento, variante. Ex: 1.0 MPI, 1.0 TSI Comfortline, LTZ 1.4 Turbo, Platinum Plus 1.0 TGDI Flex, Longitude 2.0 Diesel 4x4\n\n"
            . "Se o campo modelo contiver dados que deveriam estar na versão, SEPARE-OS.\n"
            . "Se o campo marca tiver grafia errada, CORRIJA.\n"
            . "SEMPRE retorne os campos corrigidos, mesmo se já estiverem certos (retorne o valor atual nesse caso).\n\n"
            . "Dados do veículo cadastrado:\n"
            . "- Marca: {$data['marca']}\n"
            . "- Modelo: {$data['modelo']}\n"
            . "- Versão: " . ($data['versao'] ?? 'não informada') . "\n"
            . "- Ano Fab/Mod: {$data['ano_fabricacao']}/{$data['ano_modelo']}\n"
            . "- KM: " . ($data['km'] ?? 'não informado') . "\n"
            . "- Cor: " . ($data['cor'] ?? 'não informada') . "\n"
            . "- Combustível: " . ($data['combustivel'] ?? 'não informado') . "\n"
            . "- Transmissão: " . ($data['transmissao'] ?? 'não informada') . "\n"
            . "- Motorização: " . ($data['motorizacao'] ?? 'não informada') . "\n"
            . "- Portas: " . ($data['portas'] ?? 'não informado') . "\n"
            . "- Categoria: " . ($data['categoria'] ?? 'não informada') . "\n"
            . "- Preço de venda: " . ($data['preco'] ?? 'não informado') . "\n"
            . "- Preço de compra: " . ($data['preco_compra'] ?? 'não informado') . "\n"
            . "- Descrição: " . ($data['descricao'] ?: 'vazia') . "\n\n"
            . "Responda APENAS com JSON no formato abaixo. TODOS os campos são obrigatórios:\n"
            . "{\n"
            . "  \"marca\": \"grafia oficial da marca\",\n"
            . "  \"modelo\": \"apenas o nome do modelo, sem versão\",\n"
            . "  \"versao\": \"versão/acabamento/motorização separados do modelo\",\n"
            . "  \"cor\": \"cor corrigida com inicial maiúscula\",\n"
            . "  \"motorizacao\": \"motorização correta (ex: 1.0, 1.6, 2.0 Turbo) ou null se não souber\",\n"
            . "  \"descricao_sugerida\": \"descrição atrativa para o site com 2-3 frases destacando pontos fortes do veículo (sempre gerar, mesmo se já tiver descrição)\",\n"
            . "  \"alertas\": [\"lista de inconsistências ou problemas encontrados, ou array vazio\"],\n"
            . "  \"dicas\": [\"lista de sugestões para melhorar o anúncio, ou array vazio\"]\n"
            . "}\n"
            . "Sem explicações, apenas o JSON.";

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 8192,
                    'thinkingConfig' => ['thinkingBudget' => 0],
                ],
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Erro na API Gemini: ' . $response->status()], 500);
            }

            $parts = $response->json('candidates.0.content.parts', []);
            $text = '';
            foreach ($parts as $part) {
                if (!($part['thought'] ?? false)) {
                    $text .= ($part['text'] ?? '');
                }
            }

            $start = strpos($text, '{');
            $end   = strrpos($text, '}');

            if ($start === false || $end === false) {
                return response()->json(['error' => 'Não foi possível interpretar a resposta da IA.'], 422);
            }

            $review = json_decode(substr($text, $start, $end - $start + 1), true);

            if (!is_array($review)) {
                return response()->json(['error' => 'Resposta inválida da IA.'], 422);
            }

            return response()->json(['review' => $review]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao consultar IA: ' . $e->getMessage()], 500);
        }
    }
}
