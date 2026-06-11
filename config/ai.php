<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LLM Provider
    |--------------------------------------------------------------------------
    | Supported: "gemini", "openrouter", "ollama"
    | Reads LLM_PROVIDER first, falls back to legacy AI_LLM_DRIVER.
    */
    'llm_provider' => env('LLM_PROVIDER', env('AI_LLM_DRIVER', 'gemini')),

    /*
    |--------------------------------------------------------------------------
    | Qdrant Vector Store
    |--------------------------------------------------------------------------
    */
    'qdrant' => [
        'url' => env('QDRANT_URL', 'http://localhost:6333'),
        'api_key' => env('QDRANT_API_KEY', ''),
        'collection' => env('QDRANT_COLLECTION', 'hcmue_academic_chunks'),
        'vector_size' => (int) env('QDRANT_VECTOR_SIZE', 1024),
        'end_point' => env('QDRANT_END_POINT', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval
    |--------------------------------------------------------------------------
    */
    'retrieval' => [
        'top_k' => (int) env('AI_RETRIEVAL_TOP_K', 8),
        'rerank_top_k' => (int) env('AI_RERANK_TOP_K', 5),
        'min_score' => (float) env('AI_MIN_RETRIEVAL_SCORE', 0.55),
        // Larger candidate pool when intent is total_credits to improve recall
        'total_credits_top_k' => (int) env('AI_TOTAL_CREDITS_TOP_K', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding
    |--------------------------------------------------------------------------
    | Supported provider: "gemini", "bge_m3"
    | When EMBEDDING_PROVIDER=bge_m3, Laravel calls BGE_EMBEDDING_URL instead of Gemini.
    */
    'embedding' => [
        'provider' => env('EMBEDDING_PROVIDER', 'gemini'),
        'model' => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001'),
        'dimensions' => (int) env('GEMINI_EMBEDDING_DIMENSIONS', 1024),
    ],

    /*
    |--------------------------------------------------------------------------
    | BGE-M3 Embedding (Hugging Face Space)
    |--------------------------------------------------------------------------
    */
    'bge_m3' => [
        'url' => env('BGE_EMBEDDING_URL', 'https://ntkhoi2005-hcmue-bge-m3-embedding.hf.space'),
        'timeout' => (int) env('BGE_EMBEDDING_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'api_keys' => [
            env('GEMINI_API_KEY_1', ''),
            env('GEMINI_API_KEY_2', ''),
            env('GEMINI_API_KEY_3', ''),
            env('GEMINI_API_KEY_4', ''),
            env('GEMINI_API_KEY_5', ''),
            env('GEMINI_API_KEY_6', ''),
            env('GEMINI_API_KEY_7', ''),
            env('GEMINI_API_KEY_8', ''),
            env('GEMINI_API_KEY_9', ''),
            env('GEMINI_API_KEY_10', ''),
            env('GEMINI_API_KEY_11', ''),
            env('GEMINI_API_KEY_12', ''),
            env('GEMINI_API_KEY_13', ''),
            env('GEMINI_API_KEY_14', ''),
            env('GEMINI_API_KEY_15', ''),
            env('GEMINI_API_KEY_16', ''),
            env('GEMINI_API_KEY_17', ''),
            env('GEMINI_API_KEY_18', ''),
            env('GEMINI_API_KEY_19', ''),
            env('GEMINI_API_KEY_20', ''),
            env('GEMINI_API_KEY_21', ''),
            env('GEMINI_API_KEY_22', ''),
            env('GEMINI_API_KEY_23', ''),
            env('GEMINI_API_KEY_24', ''),
            env('GEMINI_API_KEY_25', ''),
            env('GEMINI_API_KEY_26', ''),
            env('GEMINI_API_KEY_27', ''),
            env('GEMINI_API_KEY_28', ''),
            env('GEMINI_API_KEY_29', ''),
            env('GEMINI_API_KEY_30', ''),
        ],
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
        'timeout' => (int) env('GEMINI_TIMEOUT_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter
    |--------------------------------------------------------------------------
    */
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', ''),
        'model' => env('OPENROUTER_VISION_MODEL', 'google/gemini-2.0-flash'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'timeout' => (int) env('OPENROUTER_TIMEOUT_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ollama (Local LLM)
    |--------------------------------------------------------------------------
    | OLLAMA_CHAT_MODEL   — model used for chatbot generation (separate from
    |                       OLLAMA_MODEL used by AI verification).
    | OLLAMA_FALLBACK_*   — if Ollama fails and fallback enabled, use cloud.
    */
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'chat_model' => env('OLLAMA_CHAT_MODEL', 'gemma4:e2b'),
        'timeout' => (int) env('OLLAMA_TIMEOUT_SECONDS', 120),
        'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.2),
        'top_p' => (float) env('OLLAMA_TOP_P', 0.9),
        'num_ctx' => (int) env('OLLAMA_NUM_CTX', 4096),
        'num_predict' => (int) env('OLLAMA_NUM_PREDICT', 1024),
        'fallback_enabled' => (bool) env('OLLAMA_FALLBACK_ENABLED', true),
        'fallback_provider' => env('OLLAMA_FALLBACK_PROVIDER', 'gemini'),
        // Prompt compaction for constrained RAM
        'rag_top_k' => (int) env('OLLAMA_RAG_TOP_K', 4),
        'max_context_chars' => (int) env('OLLAMA_MAX_CONTEXT_CHARS', 12000),
        'include_chat_history' => (bool) env('OLLAMA_INCLUDE_CHAT_HISTORY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Evaluation Thresholds (per provider)
    |--------------------------------------------------------------------------
    */
    'evaluation' => [
        'ollama' => [
            'pass_threshold' => (float) env('OLLAMA_EVALUATION_PASS_THRESHOLD', 0.70),
            'citation_pass_threshold' => (float) env('OLLAMA_CITATION_PASS_THRESHOLD', 0.75),
        ],
    ],
];
