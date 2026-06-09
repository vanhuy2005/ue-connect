<?php

namespace App\AI\HcmueChatbot\Prompts;

class HallucinationGuardPrompt
{
    public static function render(array $vars = []): string
    {
        $draft = $vars['answer_draft'] ?? '';
        $context = $vars['context_used'] ?? '';

        $template = <<<'TXT'
Bạn là Hallucination Guard cho HCMUE Chatbot.

Nhiệm vụ:
Kiểm tra xem bản nháp câu trả lời có chứa thông tin sai lệch, tự bịa hoặc không thể suy diễn từ `context_used` không.

Quy tắc kiểm tra:
1. Mọi con số (tín chỉ, mã học phần, điểm số, học kỳ) phải khớp hoàn toàn với `context_used`.
2. Không chấp nhận thông tin "có vẻ đúng" nhưng không có trong ngữ cảnh.
3. Không tự chế các bước/quy trình học vụ.

Schema JSON trả về:
{
  "has_hallucinations": true | false,
  "detected_hallucinations": [
    {
      "statement": "câu bịa đặt trong câu trả lời",
      "reason": "không tìm thấy thông tin này trong nguồn"
    }
  ],
  "confidence_score": 0.0,
  "action": "pass | block | rewrite"
}

Bản nháp câu trả lời:
{{answer_draft}}

Nguồn ngữ cảnh (context_used):
{{context_used}}
TXT;

        $template = str_replace('{{answer_draft}}', $draft, $template);

        return str_replace('{{context_used}}', $context, $template);
    }
}
