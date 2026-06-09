<?php

namespace App\Http\Controllers\Admin;

use App\AI\HcmueChatbot\Ingestion\SourceDocumentUploadService;
use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\Http\Controllers\Controller;
use App\Jobs\IngestAcademicDocumentJob;
use App\Models\SourceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSourceDocumentController extends Controller
{
    /**
     * Display a listing of source documents.
     */
    public function index()
    {
        $documents = SourceDocument::withCount('chunks')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.source-documents.index', compact('documents'));
    }

    /**
     * Store a newly created source document.
     */
    public function store(Request $request, SourceDocumentUploadService $uploadService)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:pdf,txt,md|max:20480', // Max 20MB
            'title' => 'nullable|string|max:255',
            'document_type' => 'required|string|max:100',
            'cohort' => 'nullable|string|max:100',
            'effective_year' => 'nullable|integer|min:2000|max:2100',
            'source_url' => 'nullable|url|max:1000',
        ]);

        $file = $request->file('file');

        $metadata = [
            'document_type' => $data['document_type'],
            'title' => $data['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'cohort' => $data['cohort'] ?? null,
            'effective_year' => $data['effective_year'] ?? null,
            'source_url' => $data['source_url'] ?? null,
            'uploaded_by' => auth()->id(),
        ];

        try {
            $document = $uploadService->upload($file, $metadata);

            // Dispatch async job to ingest the document
            IngestAcademicDocumentJob::dispatch($document->id);

            return redirect()
                ->route('admin.source-documents.index')
                ->with('status', 'Tài liệu đã được tải lên thành công và đang được xử lý trong nền.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['file' => 'Lỗi tải lên hoặc xử lý tài liệu: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Trigger ingestion/re-ingestion for a source document.
     */
    public function ingest(SourceDocument $sourceDocument)
    {
        try {
            // Set status to pending/processing and dispatch job
            $sourceDocument->update(['status' => 'uploaded']);

            IngestAcademicDocumentJob::dispatch($sourceDocument->id);

            return redirect()
                ->route('admin.source-documents.index')
                ->with('status', 'Đã yêu cầu xử lý tài liệu "'.$sourceDocument->title.'". Tiến trình đang chạy trong nền.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.source-documents.index')
                ->withErrors(['error' => 'Lỗi bắt đầu xử lý: '.$e->getMessage()]);
        }
    }

    /**
     * Delete a source document, its local file, and Qdrant points.
     */
    public function destroy(SourceDocument $sourceDocument, QdrantVectorStore $vectorStore)
    {
        try {
            // 1. Delete points from Qdrant
            $chunkIds = $sourceDocument->chunks()->pluck('id')->toArray();
            if (! empty($chunkIds)) {
                $vectorStore->delete($chunkIds);
            }

            // 2. Delete document file from disk
            if ($sourceDocument->file_path) {
                Storage::disk('local')->delete($sourceDocument->file_path);
            }

            // 3. Delete from DB (foreign key Cascade or NoAction handled in code)
            $sourceDocument->chunks()->delete();
            $sourceDocument->delete();

            return redirect()
                ->route('admin.source-documents.index')
                ->with('status', 'Tài liệu và dữ liệu vector liên quan đã được xóa hoàn toàn.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.source-documents.index')
                ->withErrors(['error' => 'Lỗi xóa tài liệu: '.$e->getMessage()]);
        }
    }

    /**
     * Test RAG Search from the admin dashboard.
     */
    public function testSearch(Request $request, RagRetrievalService $retrievalService)
    {
        $query = $request->input('query', '');
        $cohort = $request->input('cohort', '');
        $documentType = $request->input('document_type', '');

        $filters = array_filter([
            'cohort' => $cohort,
            'document_type' => $documentType,
        ]);

        $results = [];
        if (! empty($query)) {
            $results = $retrievalService->retrieve($query, $filters);
        }

        return view('admin.source-documents.test-search', compact('results', 'query', 'cohort', 'documentType'));
    }
}
