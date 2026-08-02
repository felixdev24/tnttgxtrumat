<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Gửi webhook đến tất cả các URL đã đăng ký cho sự kiện tương ứng.
     * 
     * @param string $event Tên sự kiện (vd: 'student_created', 'absence_alert')
     * @param array $payload Dữ liệu đính kèm
     */
    public static function dispatch(string $event, array $payload = []): void
    {
        // Lấy các webhook đang hoạt động và có chứa event này trong mảng events
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        $data = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $payload,
        ];

        foreach ($webhooks as $webhook) {
            try {
                $request = Http::timeout(5);
                
                // Nếu có secret, thêm vào header để xác thực HMAC SHA256 (như GitHub webhooks)
                if (!empty($webhook->secret)) {
                    $signature = hash_hmac('sha256', json_encode($data), $webhook->secret);
                    $request->withHeaders([
                        'X-TNTT-Signature' => $signature,
                    ]);
                }

                $response = $request->post($webhook->url, $data);

                if (!$response->successful()) {
                    Log::warning("[Webhook] Thất bại khi gửi event {$event} đến {$webhook->url}", [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("[Webhook] Lỗi kết nối khi gửi event {$event} đến {$webhook->url}: " . $e->getMessage());
            }
        }
    }
}
