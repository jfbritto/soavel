<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class VehicleSeeder extends Seeder
{
    /**
     * Dados dos 9 veículos já existentes na landing page estática.
     * Imagens em public/img/veiculos/{pasta}/
     */
    private array $vehicles = [
        [
            'marca'          => 'Hyundai',
            'modelo'         => 'HB20',
            'versao'         => '1.0 Evolution',
            'ano_fabricacao' => 2022,
            'ano_modelo'     => 2023,
            'km'             => 28000,
            'preco'          => 69900,
            'preco_compra'   => 60000,
            'cor'            => 'Branco',
            'combustivel'    => 'flex',
            'transmissao'    => 'manual',
            'portas'         => 4,
            'motorizacao'    => '1.0',
            'categoria'      => 'hatch',
            'descricao'      => 'HB20 em excelente estado, único dono, manual e chave reserva.',
            'destaque'       => true,
            'foto_pasta'     => 'hb20-1',
        ],
        [
            'marca'          => 'Chevrolet',
            'modelo'         => 'Onix',
            'versao'         => 'LT 1.0 Turbo',
            'ano_fabricacao' => 2021,
            'ano_modelo'     => 2022,
            'km'             => 45000,
            'preco'          => 74900,
            'preco_compra'   => 65000,
            'cor'            => 'Prata',
            'combustivel'    => 'flex',
            'transmissao'    => 'automatico',
            'portas'         => 4,
            'motorizacao'    => '1.0 Turbo',
            'categoria'      => 'hatch',
            'descricao'      => 'Onix turbo automático, revisões em dia, garantia de fábrica.',
            'destaque'       => true,
            'foto_pasta'     => 'onix-1',
        ],
        [
            'marca'          => 'Chevrolet',
            'modelo'         => 'Onix Plus',
            'versao'         => 'Premier 1.0 Turbo',
            'ano_fabricacao' => 2022,
            'ano_modelo'     => 2022,
            'km'             => 32000,
            'preco'          => 89900,
            'preco_compra'   => 79000,
            'cor'            => 'Preto',
            'combustivel'    => 'flex',
            'transmissao'    => 'automatico',
            'portas'         => 4,
            'motorizacao'    => '1.0 Turbo',
            'categoria'      => 'sedan',
            'descricao'      => 'Onix Plus Premier turbo, versão topo de linha, multimídia e couro.',
            'destaque'       => true,
            'foto_pasta'     => 'onix-2',
        ],
        [
            'marca'          => 'Chevrolet',
            'modelo'         => 'Onix',
            'versao'         => 'Joy 1.0',
            'ano_fabricacao' => 2020,
            'ano_modelo'     => 2021,
            'km'             => 58000,
            'preco'          => 59900,
            'preco_compra'   => 51000,
            'cor'            => 'Vermelho',
            'combustivel'    => 'flex',
            'transmissao'    => 'manual',
            'portas'         => 4,
            'motorizacao'    => '1.0',
            'categoria'      => 'hatch',
            'descricao'      => 'Onix Joy econômico e bem conservado, ideal para o dia a dia.',
            'destaque'       => false,
            'foto_pasta'     => 'onix-3',
        ],
        [
            'marca'          => 'Volkswagen',
            'modelo'         => 'Voyage',
            'versao'         => 'Comfortline 1.6',
            'ano_fabricacao' => 2019,
            'ano_modelo'     => 2020,
            'km'             => 72000,
            'preco'          => 52900,
            'preco_compra'   => 44000,
            'cor'            => 'Branco',
            'combustivel'    => 'flex',
            'transmissao'    => 'manual',
            'portas'         => 4,
            'motorizacao'    => '1.6',
            'categoria'      => 'sedan',
            'descricao'      => 'Voyage espaçoso e confortável, ótimo para família.',
            'destaque'       => false,
            'foto_pasta'     => 'voyage-1',
        ],
        [
            'marca'          => 'Fiat',
            'modelo'         => 'Strada',
            'versao'         => 'Freedom 1.3',
            'ano_fabricacao' => 2021,
            'ano_modelo'     => 2022,
            'km'             => 38000,
            'preco'          => 94900,
            'preco_compra'   => 84000,
            'cor'            => 'Cinza',
            'combustivel'    => 'flex',
            'transmissao'    => 'manual',
            'portas'         => 2,
            'motorizacao'    => '1.3',
            'categoria'      => 'pickup',
            'descricao'      => 'Strada cabine simples, ótima para trabalho e aventura.',
            'destaque'       => true,
            'foto_pasta'     => 'strada-1',
        ],
        [
            'marca'          => 'Ford',
            'modelo'         => 'Fiesta',
            'versao'         => 'SE 1.6',
            'ano_fabricacao' => 2017,
            'ano_modelo'     => 2018,
            'km'             => 88000,
            'preco'          => 39900,
            'preco_compra'   => 32000,
            'cor'            => 'Azul',
            'combustivel'    => 'flex',
            'transmissao'    => 'manual',
            'portas'         => 4,
            'motorizacao'    => '1.6',
            'categoria'      => 'hatch',
            'descricao'      => 'Fiesta clássico, bem conservado, direção elétrica e ar-condicionado.',
            'destaque'       => false,
            'foto_pasta'     => 'fiesta-1',
        ],
        [
            'marca'          => 'Jeep',
            'modelo'         => 'Compass',
            'versao'         => 'Longitude 2.0 Flex',
            'ano_fabricacao' => 2020,
            'ano_modelo'     => 2021,
            'km'             => 52000,
            'preco'          => 139900,
            'preco_compra'   => 124000,
            'cor'            => 'Cinza',
            'combustivel'    => 'flex',
            'transmissao'    => 'automatico',
            'portas'         => 4,
            'motorizacao'    => '2.0',
            'categoria'      => 'suv',
            'descricao'      => 'Compass automático, completo, multimídia com CarPlay e Android Auto.',
            'destaque'       => true,
            'foto_pasta'     => 'compass-1',
        ],
        [
            'marca'          => 'Honda',
            'modelo'         => 'Biz',
            'versao'         => '125 ES',
            'ano_fabricacao' => 2022,
            'ano_modelo'     => 2022,
            'km'             => 12000,
            'preco'          => 12900,
            'preco_compra'   => 10500,
            'cor'            => 'Vermelho',
            'combustivel'    => 'flex',
            'transmissao'    => 'automatizado',
            'portas'         => 0,
            'motorizacao'    => '125',
            'categoria'      => 'outro',
            'descricao'      => 'Honda Biz 125 semi-nova, ótima para entrega e cidade.',
            'destaque'       => false,
            'foto_pasta'     => 'biz-1',
        ],
    ];

    public function run()
    {
        foreach ($this->vehicles as $data) {
            $fotoPasta = $data['foto_pasta'];
            unset($data['foto_pasta']);

            $data['slug'] = Vehicle::generateSlug($data['marca'], $data['modelo'], $data['ano_modelo']);

            $vehicle = Vehicle::create($data);

            // Migra fotos da pasta public/img/veiculos/{pasta}/ para storage
            $publicPath = public_path("img/veiculos/{$fotoPasta}");

            if (File::isDirectory($publicPath)) {
                $images = collect(File::files($publicPath))
                    ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp']))
                    ->values();

                foreach ($images as $index => $file) {
                    $filename  = $file->getFilename();
                    $storagePath = "vehicles/{$vehicle->id}/{$filename}";

                    Storage::disk('public')->makeDirectory("vehicles/{$vehicle->id}");
                    File::copy($file->getPathname(), storage_path("app/public/{$storagePath}"));

                    VehiclePhoto::create([
                        'vehicle_id' => $vehicle->id,
                        'path'       => $storagePath,
                        'ordem'      => $index,
                        'principal'  => $index === 0,
                    ]);
                }
            }

            $this->command->info("Veículo criado: {$vehicle->titulo} [{$vehicle->slug}]");
        }
    }
}
