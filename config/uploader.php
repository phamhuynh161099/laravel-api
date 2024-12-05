<?php
    return [
        'baseDir' => storage_path('app/public/uploads'),
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'webpp', 'png', 'gif', 'svg'],
            'file' => ['pdf', 'doc', 'docx', 'zip', 'rar'],
            'media' => ['mp3', 'mp4', 'avi']
        ],
        'max_file_size' => 1024 * 10,
        'image_constraints' => [
            'max_width' => 2000,
            'max_height' => 2000 //px
        ],
        'thumb_size' => [
            'small' => [
                'width' => 150,
                'height' => 150
            ],
            'medium' => [
                'width' => 700,
                'height' => 1200
            ]
        ],
        'default_folder' => [
            'image' => ['image', 'thumb'],
            'file' => ['file'],
            'media' => ['media']
        ]
    ];
