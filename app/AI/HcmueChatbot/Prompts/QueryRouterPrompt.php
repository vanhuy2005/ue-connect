<?php

namespace App\AI\HcmueChatbot\Prompts;

class QueryRouterPrompt
{
    public static function render(array $vars = []): string
    {
        $question = $vars['user_question'] ?? '';

        $template = <<<'TXT'
Bạn là Query Router cho HCMUE Chatbot.

Nhiệm vụ:
Phân loại câu hỏi của người dùng để quyết định nguồn dữ liệu cần dùng.

Các nguồn dữ liệu có thể dùng:

1. structured_db
Dùng cho:
- Chương trình đào tạo
- Danh sách môn học
- Mã học phần
- Tên học phần
- Số tín chỉ
- Học kỳ
- Khóa tuyển sinh
- Khoa
- Ngành
- Chuẩn đầu ra nếu đã được lưu dạng bảng
- Môn bắt buộc / tự chọn
- Tổng số tín chỉ
- Môn tiên quyết

2. rag
Dùng cho:
- Sổ tay sinh viên
- Quy chế đào tạo
- Quy định học vụ
- Cảnh báo học tập
- Điều kiện tốt nghiệp
- Học lại
- Học cải thiện
- Học bổng
- Rèn luyện
- Quy định đăng ký học phần
- Quy trình hành chính
- Giải thích khái niệm học vụ

3. hybrid
Dùng khi câu hỏi cần cả structured_db và rag.
Ví dụ:
- "Ngành CNTT khóa 51 có môn tự chọn không và học phần tự chọn nghĩa là gì?"
- "Em học ngành Sư phạm Tin khóa 50 thì điều kiện tốt nghiệp là gì?"
- "Môn này bao nhiêu tín chỉ và nếu rớt thì xử lý sao?"

4. clarification
Dùng khi thiếu thông tin quan trọng như khóa, ngành, khoa, hệ đào tạo.

5. unsupported
Dùng khi câu hỏi nằm ngoài phạm vi HCMUE Academic Assistant.

Hãy trả về JSON hợp lệ, không giải thích thêm.

Schema:
{
  "intent": "training_program_lookup | curriculum_course_lookup | academic_policy | graduation_requirement | handbook_explanation | hybrid | clarification | unsupported",
  "source": "structured_db | rag | hybrid | none",
  "confidence": 0.0,
  "entities": {
    "cohort": null,
    "admission_year": null,
    "faculty": null,
    "major": null,
    "course_code": null,
    "course_name": null,
    "semester": null,
    "policy_topic": null
  },
  "missing_required_fields": [],
  "reason": "ngắn gọn"
}

Câu hỏi người dùng:
{{user_question}}
TXT;

        return str_replace('{{user_question}}', $question, $template);
    }
}
