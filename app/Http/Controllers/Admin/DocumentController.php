<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'type' => ['nullable', 'string', 'max:60'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path         = $file->store("documents/{$patient->id}", 'public');

        Document::create([
            'patient_id'    => $patient->id,
            'type'          => $data['type'],
            'original_name' => $originalName,
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'size_bytes'    => $file->getSize(),
            'uploaded_by'   => auth()->id(),
        ]);

        return back()->with('ok_docs', 'Documento subido correctamente.');
    }

    public function destroy(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('ok_docs', 'Documento eliminado.');
    }
}
