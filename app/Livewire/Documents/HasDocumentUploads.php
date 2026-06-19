<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait HasDocumentUploads
{
    private const MAX_FILE_SIZE = 20480;

    private const MAX_UPLOADS = 10;

    public $newDocuments = [];

    public function updatedNewDocuments(): void
    {
        $this->authorize('create', Document::class);

        try {
            $this->validate([
                'newDocuments' => 'array|max:' . self::MAX_UPLOADS,
                'newDocuments.*' => 'required|file|max:' . self::MAX_FILE_SIZE . '|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv',
            ], [
                'newDocuments.max' => 'You cannot upload more than ' . self::MAX_UPLOADS . ' files at once.',
                'newDocuments.*.mimes' => 'Invalid file type. Only documents, spreadsheets, and images are allowed.',
                'newDocuments.*.max' => 'File size must not exceed 20MB.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('newDocuments');
            $error = collect($e->errors())->flatten()->first() ?? 'Invalid file upload.';
            Flux::toast($error, variant: 'danger');

            return;
        }

        $uploadedPaths = [];
        $documentsData = [];

        try {
            foreach ($this->newDocuments as $doc) {
                $originalName = $doc->getClientOriginalName();
                $mimeType = $doc->getMimeType();
                $size = $doc->getSize();

                $path = $doc->store($this->uploadDirectory());
                $uploadedPaths[] = $path;

                $documentsData[] = [
                    'client_id' => $this->client->id,
                    'name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'path' => $path,
                    'created_by' => auth()->id(),
                ];
            }

            DB::transaction(function () use ($documentsData) {
                foreach ($documentsData as $data) {
                    Document::create($data);
                }
            });
        } catch (\Throwable $e) {
            foreach ($uploadedPaths as $path) {
                Storage::delete($path);
            }
            Flux::toast('An error occurred during upload. Please try again.', variant: 'danger');

            return;
        }

        $this->reset('newDocuments');
        $this->client->refresh();
        $this->dispatch('document-uploaded');
        Flux::toast('Document(s) uploaded successfully.', variant: 'success');
    }

    private function uploadDirectory(): string
    {
        return "client-documents/{$this->client->id}";
    }
}
