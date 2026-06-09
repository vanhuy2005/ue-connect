<?php

namespace App\AI\HcmueChatbot\Prompts;

class CitationVerifierPrompt
{
    public static function render(array $vars = []): string
    {
        $draft = $vars['answer_draft'] ?? '';
        $context = $vars['context_used'] ?? '';

        $template = <<<'TXT'
Bạn là Citation Verifier cho HCMUE Chatbot.

Nhiệm vụ:
Kiểm tra xem các trích dẫn (citations, nguồn tài liệu) trong bản nháp câu trả lời có khớp với dữ liệu thực tế đã được cung cấp không.

Quy tắc:
- Mọi thông tin ghi nguồn (Ví dụ: [Sổ tay SV 2024, trang 12] hoặc [Quyết định 1234]) phải tồn tại thực tế trong `context_used`.
- Nếu có trích dẫn nào không có trong `context_used`, đó là trích dẫn giả.
- Không cho phép trích dẫn chung chung như [Internet] hoặc [Google] hoặc [Nguồn khác].

Schema JSON trả về:
{
  "all_citations_valid": true | false,
  "invalid_citations": [
    {
      "citation": "tên citation giả",
      "reason": "không có trong nguồn cung cấp"
    }
  ],
  "corrected_citations": [
    {
      "old": "citation cũ",
      "new": "citation đúng hoặc null để loại bỏ"
    }
  ]
}

Bản nháp câu trả lời:
{{answer_draft}}

Nguồn dữ liệu thực tế (context_used):
{{context_used}}
TXT;

        $template = str_replace('{{answer_draft}}', $draft, $template);

        return str_replace('{{context_used}}', $context, $template);
    }
}
