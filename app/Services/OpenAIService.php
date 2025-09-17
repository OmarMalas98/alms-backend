<?php

// app\Services\OpenAIService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function sendMessage($systemMessage, $userMessage)
    {

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("https://api.openai.com/v1/chat/completions",[
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemMessage,
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage,
                ],
            ],
            'temperature' => 1,
        ] );
    }
}
