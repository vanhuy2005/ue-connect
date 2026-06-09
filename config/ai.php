<?php

return [
    'qdrant' => [
        'url' => env('QDRANT_URL', 'http://localhost:6333'),
        'api_key' => env('QDRANT_API_KEY', ''),
        'collection' => env('QDRANT_COLLECTION', 'hcmue_academic_chunks'),
    ],
    'retrieval' => [
        'top_k' => (int) env('AI_RETRIEVAL_TOP_K', 8),
        'rerank_top_k' => (int) env('AI_RERANK_TOP_K', 5),
        'min_score' => (float) env('AI_MIN_RETRIEVAL_SCORE', 0.65),
    ],
    'embedding' => [
        'model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    ],
];
