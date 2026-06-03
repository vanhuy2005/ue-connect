<?php

return [
    'title' => 'Nhật ký thao tác',
    'subtitle' => 'Theo dõi các hành động quản trị nhạy cảm đã được ghi nhận.',
    'filters' => [
        'actor_id' => 'ID người thực hiện',
        'action' => 'Hành động',
        'filter' => 'Lọc',
        'clear' => 'Xóa lọc',
    ],
    'table' => [
        'when' => 'Thời gian',
        'actor' => 'Người thực hiện',
        'action' => 'Hành động',
        'target' => 'Đối tượng',
        'reason' => 'Lý do',
        'empty' => 'Không có nhật ký nào.',
    ],
    'pagination' => [
        'previous' => 'Trước',
        'next' => 'Sau',
    ],
];
