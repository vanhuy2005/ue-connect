<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Ingestion\SourceDocumentUploadService;
use App\Models\SourceDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SourceDocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test uploading a document via HTTP UploadedFile.
     */
    public function test_can_upload_source_document_via_uploaded_file(): void
    {
        $service = new SourceDocumentUploadService;
        $fakeFile = UploadedFile::fake()->create('so_tay_sinh_vien.pdf', 1024, 'application/pdf');

        $metadata = [
            'document_type' => 'student_handbook',
            'title' => 'Sổ tay sinh viên 2026',
            'cohort' => 'K50',
            'effective_year' => 2026,
            'source_url' => 'https://hcmue.edu.vn/so-tay',
        ];

        $document = $service->upload($fakeFile, $metadata);

        $this->assertInstanceOf(SourceDocument::class, $document);
        $this->assertEquals('student_handbook', $document->document_type);
        $this->assertEquals('Sổ tay sinh viên 2026', $document->title);
        $this->assertEquals('K50', $document->cohort);
        $this->assertEquals(2026, $document->effective_year);
        $this->assertEquals('uploaded', $document->status);

        $this->assertNotEmpty($document->file_path);
        Storage::disk('local')->assertExists($document->file_path);

        $this->assertDatabaseHas('source_documents', [
            'id' => $document->id,
            'status' => 'uploaded',
        ]);
    }

    /**
     * Test uploading a document via a local file path.
     */
    public function test_can_upload_source_document_via_local_path(): void
    {
        $service = new SourceDocumentUploadService;

        // Create a temporary file in our system to simulate local file upload
        $tempFile = tempnam(sys_get_temp_dir(), 'test_doc');
        file_put_contents($tempFile, '# Nội quy học vụ HCMUE');

        $metadata = [
            'document_type' => 'regulation',
            'title' => 'Quy chế học tập',
        ];

        try {
            $document = $service->upload($tempFile, $metadata);

            $this->assertInstanceOf(SourceDocument::class, $document);
            $this->assertEquals('regulation', $document->document_type);
            $this->assertEquals('Quy chế học tập', $document->title);
            $this->assertEquals('uploaded', $document->status);
            Storage::disk('local')->assertExists($document->file_path);

            // Verify file content was copied
            $this->assertEquals('# Nội quy học vụ HCMUE', Storage::disk('local')->get($document->file_path));
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
