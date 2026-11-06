<?php

return [
    'title' => 'Masuk',
    'heading' => 'Masuk ke akun Anda',
    'actions' => [
        'register' => [
            'before' => 'atau',
            'label' => 'daftar akun baru',
        ],
        'request_password_reset' => [
            'label' => 'Lupa kata sandi Anda?',
        ],
    ],
    'form' => [
        'email' => [
            'label' => 'Alamat email',
        ],
        'password' => [
            'label' => 'Kata sandi',
        ],
        'remember' => [
            'label' => 'Ingat saya',
        ],
        'actions' => [
            'authenticate' => [
                'label' => 'Masuk',
            ],
        ],
    ],
    'messages' => [
        'failed' => 'Email atau kata sandi yang Anda masukkan salah. Silakan periksa kembali.',
    ],
    'notifications' => [
        'throttled' => [
            'title' => 'Terlalu banyak percobaan masuk',
            'body' => 'Silakan coba lagi dalam :seconds detik.',
        ],
    ],
];
