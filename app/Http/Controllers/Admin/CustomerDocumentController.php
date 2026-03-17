<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerDocumentController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'documents'   => 'required|array|min:1',
            'documents.*' => 'file|max:10240',
            'name'        => 'nullable|string|max:255',
            'categoria'   => 'required|string|in:' . implode(',', array_keys(CustomerDocument::CATEGORIAS)),
        ]);

        $created = 0;

        foreach ($request->file('documents') as $file) {
            $path = $file->store("customer-documents/{$customer->id}", 'local');

            CustomerDocument::create([
                'customer_id'   => $customer->id,
                'name'          => $request->input('name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'categoria'     => $request->input('categoria'),
            ]);

            $created++;
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $created]);
        }

        return back()->with('success', $created . ' documento(s) adicionado(s) com sucesso!');
    }

    public function destroy(Customer $customer, CustomerDocument $document)
    {
        abort_if($document->customer_id !== $customer->id, 404);

        $document->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Documento removido com sucesso.');
    }

    public function download(Customer $customer, CustomerDocument $document)
    {
        abort_if($document->customer_id !== $customer->id, 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }
}
