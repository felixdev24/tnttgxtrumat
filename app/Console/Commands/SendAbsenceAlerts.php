<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\ZaloBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendAbsenceAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:absence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo Zalo cho các đoàn sinh nghỉ quá 2 buổi giáo lý trong tháng hiện tại';

    /**
     * Execute the console command.
     */
    public function handle(ZaloBotService $zaloBot)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Lấy tất cả đoàn sinh có zalo_id
        $students = User::doanSinh()->whereNotNull('zalo_id')->get();
        $countSent = 0;

        $this->info("Bắt đầu kiểm tra và gửi cảnh báo vắng mặt tháng {$startOfMonth->format('m/Y')}...");

        foreach ($students as $student) {
            // Kiểm tra xem đã gửi thông báo trong tháng này chưa
            if ($student->last_zalo_absence_alert_at && clone $student->last_zalo_absence_alert_at->between($startOfMonth, $endOfMonth)) {
                continue;
            }

            // Đếm số buổi nghỉ giáo lý trong tháng
            $absences = AttendanceRecord::where('user_id', $student->id)
                ->where('status', 'absent')
                ->whereHas('session', function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('session_type', 'giao_ly')
                          ->whereBetween('session_date', [$startOfMonth, $endOfMonth]);
                })
                ->count();

            if ($absences > 2) {
                $monthStr = now()->format('m/Y');
                $text = "Kính gửi phụ huynh em *{$student->name}*,\n\nĐây là tin nhắn tự động từ Xứ đoàn Thiếu Nhi Thánh Thể.\nTrong tháng {$monthStr}, em đã vắng mặt *{$absences} buổi* học giáo lý.\n\nXin phụ huynh vui lòng nhắc nhở và đôn đốc em tham gia đầy đủ để việc học giáo lý không bị gián đoạn.\nTrân trọng!";
                
                $success = $zaloBot->sendMessage($student->zalo_id, $text);
                
                if ($success) {
                    $student->update(['last_zalo_absence_alert_at' => now()]);
                    $this->info("Đã gửi cảnh báo cho đoàn sinh: {$student->name} (Vắng {$absences} buổi)");
                    $countSent++;
                } else {
                    $this->error("Lỗi khi gửi cảnh báo cho: {$student->name}");
                }
            }
        }

        $this->info("Hoàn thành! Đã gửi {$countSent} cảnh báo.");
    }
}
