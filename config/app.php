<?php
// Application Settings & System Constants
return [
    'app_name'    => 'ESG-Pro 智慧管理與碳盤查系統',
    'app_version' => '1.0.0',
    'base_url'    => '/esg',
    'timezone'    => 'Asia/Taipei',
    'session_key' => 'esg_auth_user',
    'max_upload_size' => 20 * 1024 * 1024, // 20 MB
    'allowed_upload_exts' => ['pdf', 'jpg', 'jpeg', 'png', 'xlsx', 'docx'],
    'allowed_upload_mimes' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword'
    ]
];
