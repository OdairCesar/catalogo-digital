<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'lead_notifications' => [
        'email' => env('LEAD_NOTIFICATION_EMAIL', 'odairferreira97@gmail.com'),
    ],

    'openai' => [
        'model' => env('OPENAI_MODEL', 'gpt-4.1'),
        'image_model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1'),
        'image_size' => env('OPENAI_IMAGE_SIZE', '1536x1024'),
        'image_quality' => env('OPENAI_IMAGE_QUALITY', 'high'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    ],

    'gemini' => [
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
        'image_aspect_ratio' => env('GEMINI_IMAGE_ASPECT_RATIO', '16:9'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
    ],

    'ai' => [
        'text_provider' => env('AI_TEXT_PROVIDER', 'openai'),
        'image_provider' => env('AI_IMAGE_PROVIDER', 'openai'),
    ],

    'google_tag_manager' => [
        'id' => env('GOOGLE_TAG_MANAGER_ID'),
    ],

];
