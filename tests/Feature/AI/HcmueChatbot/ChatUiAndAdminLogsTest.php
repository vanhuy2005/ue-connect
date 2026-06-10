<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\Models\AiAnswer;
use App\Models\AiFeedback;
use App\Models\AiQuestion;
use App\Models\ChatSession;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ChatUiAndAdminLogsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed access control reference data if role system requires it
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Create Admin
        $this->admin = User::factory()->create([
            'email' => 'admin@teacher.hcmue.edu.vn',
            'account_status' => 'active',
        ]);
        $this->admin->assignRole('admin');

        // Create Student
        $this->student = User::factory()->create([
            'email' => 'student@student.hcmue.edu.vn',
            'account_status' => 'active',
        ]);
        $this->student->assignRole('student');
    }

    /**
     * Test the student chat interface Volt component.
     */
    public function test_student_can_use_chat_interface(): void
    {
        $this->actingAs($this->student);

        // Render component
        $component = Volt::test('pages.app.chat-page');

        $session = ChatSession::where('user_id', $this->student->id)->first();
        $this->assertNotNull($session);

        // Mock the AnswerComposerService to avoid Gemini LLM call
        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Đây là câu trả lời kiểm thử.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash',
                'latency_ms' => 10,
                'input_tokens' => 10,
                'output_tokens' => 10,
                'total_tokens' => 20,
            ]);
        });

        // Send a message
        $component->set('input', 'Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?')
            ->call('sendMessage')
            ->assertSet('isTyping', false);

        // Assert database records
        $this->assertDatabaseHas('ai_questions', [
            'session_id' => $session->id,
            'original_question' => 'Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?',
        ]);

        $question = AiQuestion::where('session_id', $session->id)->first();
        $this->assertNotNull($question);

        // The answer may include a source citation footer appended after the LLM answer text.
        // Assert the core answer text is present using a partial model check.
        $answer = AiAnswer::where('question_id', $question->id)->first();
        $this->assertNotNull($answer);
        $this->assertStringContainsString('Đây là câu trả lời kiểm thử.', $answer->answer_text);

        // Submit rating feedback
        $component->call('submitRating', $answer->id, 5)
            ->call('submitFeedback', $answer->id);

        $this->assertDatabaseHas('ai_feedback', [
            'answer_id' => $answer->id,
            'user_id' => $this->student->id,
            'rating' => 5,
        ]);
    }

    /**
     * Test the admin AI Chat Logs monitoring component.
     */
    public function test_admin_can_monitor_ai_chat_logs(): void
    {
        // Log a question with negative rating
        $session = ChatSession::create(['user_id' => $this->student->id, 'title' => 'Session 1']);
        $q1 = AiQuestion::create([
            'session_id' => $session->id,
            'user_id' => $this->student->id,
            'original_question' => 'Câu hỏi tiêu cực',
            'normalized_question' => 'câu hỏi tiêu cực',
            'intent' => 'policy',
            'source_route' => 'rag',
            'confidence' => 0.9,
        ]);
        $a1 = AiAnswer::create([
            'question_id' => $q1->id,
            'answer_text' => 'Câu trả lời 1',
            'model_provider' => 'gemini',
            'model_name' => 'gemini-1.5-flash',
            'latency_ms' => 100,
        ]);
        AiFeedback::create([
            'answer_id' => $a1->id,
            'user_id' => $this->student->id,
            'rating' => 1,
            'comment' => 'Không hài lòng',
        ]);

        // Log a fallback question
        $q2 = AiQuestion::create([
            'session_id' => $session->id,
            'user_id' => $this->student->id,
            'original_question' => 'Hôm nay ăn gì',
            'normalized_question' => 'hôm nay ăn gì',
            'intent' => 'unsupported',
            'source_route' => 'none',
            'confidence' => 1.0,
        ]);
        AiAnswer::create([
            'question_id' => $q2->id,
            'answer_text' => 'Tôi không hỗ trợ thông tin này.',
            'model_provider' => 'system',
            'model_name' => 'fallback',
            'latency_ms' => 0,
        ]);

        // Unauthorized user cannot access page
        $this->actingAs($this->student);
        $this->get(route('admin.ai-chat-logs.index'))->assertStatus(403);

        // Admin can access page
        $this->actingAs($this->admin);
        $this->get(route('admin.ai-chat-logs.index'))->assertOk();

        // Test filtering in Livewire Volt component
        $component = Volt::test('pages.admin.ai-chat-logs');

        // Filter by negative rating
        $component->set('feedbackFilter', 'negative');
        $this->assertCount(1, $component->get('questions'));

        // Filter by unanswered / fallback
        $component->set('feedbackFilter', '')
            ->set('unansweredOnly', true);
        $this->assertCount(1, $component->get('questions'));

        // Search text
        $component->set('unansweredOnly', false)
            ->set('search', 'tiêu cực');
        $this->assertCount(1, $component->get('questions'));

        // View detail
        $component->call('showDetail', $q1->id)
            ->assertSet('activeQuestionId', $q1->id);
    }
}
