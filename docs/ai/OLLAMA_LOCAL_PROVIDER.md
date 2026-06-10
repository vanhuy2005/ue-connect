# OLLAMA_LOCAL_PROVIDER.md

# HCMUE Chatbot — Ollama Local LLM Provider

Tài liệu này hướng dẫn cài đặt và sử dụng Ollama làm LLM provider cho HCMUE Chatbot.

## Vì sao dùng Ollama?

- **Privacy**: dữ liệu học vụ không rời khỏi máy.
- **Cost**: không cần API key cloud, không tốn tiền per-token.
- **Offline**: chatbot hoạt động kể cả khi mất mạng.
- **Control**: toàn quyền kiểm soát model, không phụ thuộc cloud SLA.

**Nhược điểm**: chậm hơn cloud trên máy yếu, RAM cần ≥ 8GB.

---

## 1. Cài Ollama trên Windows

Tải installer từ: https://ollama.com/download

Sau khi cài, Ollama chạy tự động khi khởi động Windows.

Kiểm tra:

```powershell
ollama --version
```

Nếu thấy version thì OK.

---

## 2. Pull model Gemma 4

```powershell
# Khuyến nghị cho máy 8GB RAM
ollama pull gemma4:e2b

# Nếu máy có RAM cao hơn (16GB+)
ollama pull gemma4:12b
```

Kiểm tra model đã cài:

```powershell
ollama list
```

---

## 3. Test Ollama CLI

```powershell
# Test chat trực tiếp
ollama run gemma4:e2b "Bạn là ai?"

# Test không tương tác
echo "Xin chào" | ollama run gemma4:e2b
```

---

## 4. Test Ollama REST API

```powershell
curl http://127.0.0.1:11434/api/tags
```

Nếu Ollama đang chạy, sẽ trả về danh sách models dạng JSON.

Test chat API:

```powershell
curl -X POST http://127.0.0.1:11434/api/chat `
  -H "Content-Type: application/json" `
  -d '{
    "model": "gemma4:e2b",
    "messages": [{"role": "user", "content": "Xin chào"}],
    "stream": false
  }'
```

---

## 5. Config .env

Thêm (hoặc cập nhật) các biến sau trong `.env`:

```env
# Chuyển sang Ollama
LLM_PROVIDER=ollama

# Ollama server (giữ nguyên nếu chạy local)
OLLAMA_BASE_URL=http://127.0.0.1:11434

# Model dùng cho chatbot (không phải AI verification)
OLLAMA_CHAT_MODEL=gemma4:e2b

# Timeout cao hơn vì model local chậm hơn cloud
OLLAMA_TIMEOUT_SECONDS=120

# Tham số generation
OLLAMA_TEMPERATURE=0.2
OLLAMA_TOP_P=0.9
OLLAMA_NUM_CTX=4096
OLLAMA_NUM_PREDICT=1024

# Fallback về Gemini nếu Ollama lỗi
OLLAMA_FALLBACK_ENABLED=true
OLLAMA_FALLBACK_PROVIDER=gemini

# Prompt compaction cho RAM thấp
OLLAMA_RAG_TOP_K=4
OLLAMA_MAX_CONTEXT_CHARS=12000
OLLAMA_INCLUDE_CHAT_HISTORY=false
```

> **LƯU Ý**: `OLLAMA_CHAT_MODEL` (cho chatbot) và `OLLAMA_MODEL` (cho AI verification) là hai biến khác nhau. Không nhầm lẫn.

---

## 6. Test Laravel command

```powershell
php artisan hcmue:ollama:test

# Test với model khác
php artisan hcmue:ollama:test --model=gemma4:12b

# Test với prompt tùy chỉnh
php artisan hcmue:ollama:test --prompt="Ngành CNTT K51 có bao nhiêu tín chỉ?"
```

Output mẫu khi thành công:

```
  HCMUE Chatbot — Ollama Local LLM Test
  ─────────────────────────────────────
  Base URL : http://127.0.0.1:11434
  Model    : gemma4:e2b
  Prompt   : Xin chào, bạn là ai?

  [1] Checking Ollama server...
  [OK] Ollama server is reachable
  [2] Checking model 'gemma4:e2b'...
  [OK] Model gemma4:e2b is installed
  [3] Sending test chat request...
  [OK] Response received in 8243ms

  ─────────────────────────────────────
  Response:
  Xin chào! Tôi là HCMUE Academic Assistant, ...

  Token usage:
    Input  : 45
    Output : 87
    Total  : 132

  ✔ All checks passed. Ollama is ready.
```

---

## 7. Chạy evaluation với Ollama

> TODO: Khi command `hcmue:evaluate` được implement, chạy:
> ```
> php artisan hcmue:evaluate --provider=ollama --model=gemma4:e2b --limit=20 --save
> ```
>
> Threshold riêng cho Ollama (xem `.env`):
> - `OLLAMA_EVALUATION_PASS_THRESHOLD=0.70`
> - `OLLAMA_CITATION_PASS_THRESHOLD=0.75`

---

## 8. Chuyển về Gemini (khi cần)

Chỉ cần thay đổi một dòng trong `.env`:

```env
LLM_PROVIDER=gemini
```

Rồi:

```powershell
php artisan config:clear
```

---

## Troubleshooting

### Connection refused

```
Ollama is not available at http://127.0.0.1:11434
```

**Nguyên nhân**: Ollama chưa chạy.

**Giải pháp**:
```powershell
ollama serve
```

Hoặc kiểm tra service trong Task Manager / Services.

---

### Model not found

```
Model 'gemma4:e2b' not found. Run: ollama pull gemma4:e2b
```

**Giải pháp**:
```powershell
ollama pull gemma4:e2b
```

---

### Timeout

```
cURL error 28: Operation timed out
```

**Nguyên nhân**: Model quá lớn hoặc máy đang bận.

**Giải pháp**:
- Tăng `OLLAMA_TIMEOUT_SECONDS=180` hoặc `240`.
- Giảm `OLLAMA_NUM_CTX=2048`.
- Dùng model nhỏ hơn (`gemma4:e2b` thay vì `gemma4:12b`).

---

### Out of memory / máy đứng

**Nguyên nhân**: Model quá lớn so với RAM.

**Giải pháp**:
- Giảm `OLLAMA_NUM_CTX=2048`.
- Đóng các tab trình duyệt và ứng dụng khác.
- Dùng `gemma4:e2b` (2B params) thay vì model lớn hơn.

---

### Response quá chậm

**Giải pháp**:
- Giảm `OLLAMA_NUM_CTX` và `OLLAMA_NUM_PREDICT`.
- Bật `OLLAMA_FALLBACK_ENABLED=true` để tự động fallback Gemini khi cần.

---

### Context quá dài / bị cắt

**Giải pháp**:
- Điều chỉnh `OLLAMA_RAG_TOP_K=3` (giảm số chunks).
- Điều chỉnh `OLLAMA_MAX_CONTEXT_CHARS=8000` (cắt ngắn hơn).

---

## Khuyến nghị model cho RAM 8GB

| Model | RAM cần | Tốc độ | Chất lượng |
|-------|---------|--------|------------|
| `gemma4:e2b` | ~3GB | Nhanh (~5-15s) | Tốt cho RAG đơn giản |
| `gemma4:e4b` | ~5GB | Trung bình | Tốt hơn, an toàn hơn |
| `gemma4:12b` | ~10GB | Chậm | Tốt nhất, cần 16GB RAM |

> **Bắt đầu với `gemma4:e2b`**. Nếu chất lượng không đủ, thử `gemma4:e4b`.

---

## Kiến trúc provider

```
LLM_PROVIDER=ollama
        ↓
LlmGateway::driverWithFallback()
        ↓
OllamaProvider::generate()
   ├── Success → trả về answer
   └── Fail + OLLAMA_FALLBACK_ENABLED=true
              ↓
       GeminiProvider::generate() (fallback)
```

Embedding **luôn dùng Gemini** trong Phase 1 — không phụ thuộc vào `LLM_PROVIDER`.
