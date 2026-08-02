<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZaloBotService
{
    protected string $botToken;

    protected string $baseUrl = 'https://bot-api.zaloplatforms.com';

    public function __construct()
    {
        $this->botToken = config('services.zalo_bot.token', '');
    }

    /**
     * Gửi tin nhắn văn bản đến một người dùng Zalo.
     *
     * @param  string  $chatId  Zalo user ID của người nhận
     * @param  string  $text  Nội dung tin nhắn (hỗ trợ Markdown)
     * @return bool
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        if (empty($this->botToken) || empty($chatId)) {
            Log::warning('[ZaloBot] Token hoặc chat_id trống, bỏ qua gửi tin nhắn.');

            return false;
        }

        $url = "{$this->baseUrl}/bot{$this->botToken}/sendMessage";

        try {
            $response = Http::timeout(10)->post($url, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'markdown',
            ]);

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                Log::warning('[ZaloBot] Gửi tin nhắn thất bại.', [
                    'chat_id' => $chatId,
                    'response' => $data,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[ZaloBot] Lỗi khi gửi tin nhắn: '.$e->getMessage(), [
                'chat_id' => $chatId,
            ]);

            return false;
        }
    }
}
