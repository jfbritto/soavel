<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'suspended' => $this->isSuspended(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function stats()
    {
        $vehiclesCount = DB::table('vehicles')->count();
        $leadsCount = DB::table('leads')->count();
        $salesCount = DB::table('vehicles')->where('status', 'vendido')->count();

        $diskUsage = 0;
        $storagePath = storage_path('app/public');
        if (is_dir($storagePath)) {
            $diskUsage = round($this->directorySize($storagePath) / 1024 / 1024, 2);
        }

        return response()->json([
            'vehicles_count' => $vehiclesCount,
            'leads_count' => $leadsCount,
            'sales_count' => $salesCount,
            'disk_usage_mb' => $diskUsage,
        ]);
    }

    public function suspend()
    {
        $this->setSetting('suspended', 'true');

        return response()->json([
            'status' => 'suspended',
            'message' => 'Site suspenso com sucesso.',
        ]);
    }

    public function reactivate()
    {
        $this->setSetting('suspended', 'false');

        return response()->json([
            'status' => 'active',
            'message' => 'Site reativado com sucesso.',
        ]);
    }

    public function config(Request $request)
    {
        $configs = $request->except(['_token']);

        foreach ($configs as $key => $value) {
            $this->setSetting($key, $value);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Configurações atualizadas.',
            'updated' => array_keys($configs),
        ]);
    }

    public function updateBilling(Request $request)
    {
        $fields = ['billing_status', 'billing_amount', 'billing_due_date', 'billing_invoice_url', 'billing_type', 'billing_subscription_status'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->setSetting($field, $request->input($field));
            }
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Dados de cobrança atualizados.',
        ]);
    }

    private function isSuspended()
    {
        return DB::table('settings')->where('key', 'suspended')->value('value') === 'true';
    }

    private function getSetting($key)
    {
        return DB::table('settings')->where('key', $key)->value('value');
    }

    private function setSetting($key, $value)
    {
        DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
    }

    private function directorySize($path)
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
}
