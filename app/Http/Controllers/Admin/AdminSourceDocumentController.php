<?php

namespace App\Http\Controllers\Admin;

use App\AI\HcmueChatbot\Chat\QueryRouterService;
use App\AI\HcmueChatbot\Ingestion\SourceDocumentUploadService;
use App\AI\HcmueChatbot\Retrieval\AcademicQueryAnalyzer;
use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\Http\Controllers\Controller;
use App\Jobs\IngestAcademicDocumentJob;
use App\Models\DocumentChunk;
use App\Models\SourceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AdminSourceDocumentController extends Controller
{
    /**
     * Display a listing of source documents.
     */
    public function index()
    {
        $documents = SourceDocument::with(['firstChunk'])
            ->withCount('chunks')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate Data Coverage Statistics in a DB-agnostic manner
        $directoryPath = base_path('database/AI');
        $filesInAI = File::exists($directoryPath)
            ? count(File::allFiles($directoryPath))
            : 0;

        $totalFiles = SourceDocument::count();
        $statusDistribution = SourceDocument::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $typeDistribution = SourceDocument::selectRaw('document_type, count(*) as count')
            ->groupBy('document_type')
            ->pluck('count', 'document_type')
            ->toArray();

        $cohortDistribution = SourceDocument::selectRaw('cohort, count(*) as count')
            ->whereNotNull('cohort')
            ->groupBy('cohort')
            ->pluck('count', 'cohort')
            ->toArray();

        // Count unique documents per faculty/major in chunk metadata
        $facultyDistribution = [];
        $majorDistribution = [];
        $chunksWithFaculty = DocumentChunk::whereNotNull('metadata_json')->get();
        foreach ($chunksWithFaculty as $chunk) {
            $meta = $chunk->metadata_json;
            if (is_string($meta)) {
                $meta = json_decode($meta, true);
            }
            $fac = $meta['faculty'] ?? 'Chung';
            if ($fac) {
                $docId = $chunk->source_document_id;
                $facultyDistribution[$fac][$docId] = true;
            }
            $maj = $meta['major'] ?? 'Chung';
            if ($maj) {
                $docId = $chunk->source_document_id;
                $majorDistribution[$maj][$docId] = true;
            }
        }
        $facultyStats = [];
        foreach ($facultyDistribution as $fac => $docs) {
            $facultyStats[$fac] = count($docs);
        }
        $majorStats = [];
        foreach ($majorDistribution as $maj => $docs) {
            $majorStats[$maj] = count($docs);
        }

        $stats = [
            'total_files_in_dir' => $filesInAI,
            'total_files' => $totalFiles,
            'file_ingested' => SourceDocument::where('status', 'active')->count(),
            'file_not_ingested' => SourceDocument::whereIn('status', ['uploaded', 'processing'])->count(),
            'file_failed' => SourceDocument::where('status', 'failed')->count(),
            'file_needs_ocr' => SourceDocument::where('status', 'needs_ocr')->count(),
            'total_chunks' => DocumentChunk::count(),
            'total_vectors' => DocumentChunk::where('embedding_status', 'success')->count(),
            'status_distribution' => $statusDistribution,
            'type_distribution' => $typeDistribution,
            'cohort_distribution' => $cohortDistribution,
            'faculty_distribution' => $facultyStats,
            'major_distribution' => $majorStats,
        ];

        return view('admin.source-documents.index', compact('documents', 'stats'));
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
     * Repair metadata of a source document and update all its chunks/Qdrant points.
     */
    public function repair(Request $request, SourceDocument $sourceDocument)
    {
        $data = $request->validate([
            'document_type' => 'required|string|max:100',
            'cohort' => 'nullable|string|max:100',
            'effective_year' => 'nullable|integer|min:2000|max:2100',
            'faculty' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
        ]);

        try {
            // Update SourceDocument DB record
            $sourceDocument->update([
                'document_type' => $data['document_type'],
                'cohort' => $data['cohort'],
                'effective_year' => $data['effective_year'],
            ]);

            // Update Chunks and Qdrant payloads
            $chunks = $sourceDocument->chunks;

            $qdrantUrl = rtrim(config('ai.qdrant.url', 'http://localhost:6333'), '/');
            $apiKey = config('ai.qdrant.api_key', '');
            $collection = config('ai.qdrant.collection', 'hcmue_academic_chunks');

            $headers = ['Content-Type' => 'application/json'];
            if (! empty($apiKey)) {
                $headers['api-key'] = $apiKey;
            }

            foreach ($chunks as $chunk) {
                $meta = $chunk->metadata_json;
                if (is_string($meta)) {
                    $meta = json_decode($meta, true);
                }

                $meta['document_type'] = $data['document_type'];
                $meta['cohort'] = $data['cohort'];
                $meta['academic_year'] = (string) $data['effective_year'];
                if (isset($data['faculty'])) {
                    $meta['faculty'] = $data['faculty'];
                }
                if (isset($data['major'])) {
                    $meta['major'] = $data['major'];
                }

                $chunk->update([
                    'metadata_json' => $meta,
                ]);

                // Call Qdrant SET PAYLOAD
                Http::withHeaders($headers)
                    ->withoutVerifying()
                    ->post("{$qdrantUrl}/collections/{$collection}/points/payload", [
                        'payload' => $meta,
                        'points' => [$chunk->id],
                    ]);
            }

            // Clear answer cache
            Cache::flush();

            return redirect()
                ->route('admin.source-documents.index')
                ->with('status', 'Metadata của tài liệu và các vector chunks đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.source-documents.index')
                ->withErrors(['error' => 'Lỗi cập nhật metadata: '.$e->getMessage()]);
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
    public function testSearch(Request $request, RagRetrievalService $retrievalService, QueryRouterService $routerService)
    {
        $query = $request->input('query', '');
        $cohort = $request->input('cohort', '');
        $documentType = $request->input('document_type', '');
        $debug = $request->has('debug') || $request->boolean('debug', false);

        $filters = array_filter([
            'cohort' => $cohort,
            'document_type' => $documentType,
        ]);

        $results = [];
        $debugData = [];

        if (! empty($query)) {
            $results = $retrievalService->retrieve($query, $filters);

            if ($debug) {
                $analyzer = new AcademicQueryAnalyzer;
                $analysis = $analyzer->analyze($query);
                $variations = $retrievalService->generateQueryVariations($query);
                $routeResult = $routerService->route($query);

                $debugData = [
                    'analysis' => $analysis,
                    'variations' => $variations,
                    'resolved_filters' => array_merge($filters, array_filter([
                        'cohort' => $analysis['cohort'],
                        'document_type' => $analysis['document_type'] !== 'unknown' ? $analysis['document_type'] : null,
                    ])),
                    'route' => $routeResult['source'],
                    'intent' => $routeResult['intent'],
                    'confidence' => $routeResult['confidence'],
                ];
            }
        }

        return view('admin.source-documents.test-search', compact('results', 'query', 'cohort', 'documentType', 'debug', 'debugData'));
    }
}
