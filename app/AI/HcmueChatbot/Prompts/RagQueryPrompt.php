<?php

namespace App\AI\HcmueChatbot\Prompts;

class RagQueryPrompt
{
    public static function render(array $vars = []): string
    {
        $normalizedQuestion = $vars['normalized_question'] ?? '';
        $routerJson = $vars['router_json'] ?? '';

        $template = <<<'TXT'
Bạn là RAG Query Generator cho HCMUE Chatbot.

Nhiệm vụ:
Tạo các truy vấn tìm kiếm tốt nhất để tìm thông tin trong:
- Sổ tay sinh viên
- Quy chế đào tạo
- Quy định học vụ
- Hướng dẫn đăng ký học phần
- Thông báo học vụ
- FAQ sinh viên
- Chuẩn đầu ra dạng văn bản

Quy tắc:
- Tạo 3 đến 5 query.
- Query phải bằng tiếng Việt.
- Có thể thêm biến thể từ đồng nghĩa.
- Không trả lời câu hỏi.
- Không thêm nội dung không có trong câu hỏi.
- Ưu tiên thuật ngữ học vụ chính thức.

Trả về JSON:
{
  "queries": [
    "...",
    "...",
    "..."
  ],
  "metadata_filters": {
    "cohort": null,
    "document_type": null,
    "effective_year": null,
    "section": null
  },
  "expected_answer_type": "definition | procedure | condition | warning | policy | comparison | unknown"
}

Câu hỏi:
{{normalized_question}}

Thông tin đã detect:
{{router_json}}
TXT;

        $template = str_replace('{{normalized_question}}', $normalizedQuestion, $template);

        return str_replace('{{router_json}}', $routerJson, $template);
    }
}
