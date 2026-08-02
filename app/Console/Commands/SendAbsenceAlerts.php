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

        // Lấy tất cả đoàn sinh
        $students = User::doanSinh()->with('tnttClass')->get();
        $absentList = [];

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
                $absentList[] = [
                    'student' => $student,
                    'absences' => $absences
                ];
            }
        }

        if (empty($absentList)) {
            $this->info("Không có đoàn sinh nào nghỉ quá 2 buổi trong tháng này (hoặc đã thông báo hết).");
            return;
        }

        $monthStr = now()->format('m/Y');
        $text = "Kính báo quý Phụ Huynh,\n\nDanh sách các em Thiếu Nhi vắng mặt *quá 2 buổi học giáo lý* trong tháng {$monthStr}:\n";

        foreach ($absentList as $index => $item) {
            $student = $item['student'];
            $className = $student->tnttClass ? $student->tnttClass->name : 'Chưa xếp lớp';
            $text .= "\n" . ($index + 1) . ". *{$student->name}* - Lớp: {$className} (Vắng: {$item['absences']} buổi)";
        }

        $text .= "\n\nXin quý phụ huynh lưu ý và đôn đốc các em tham gia đầy đủ để việc học giáo lý không bị gián đoạn. Xin cảm ơn!";

        $groupId = 'ql19hdksyqoph0b8qsup'; // ID nhóm chat theo yêu cầu

        $success = $zaloBot->sendMessage($groupId, $text);

        if ($success) {
            foreach ($absentList as $item) {
                $item['student']->update(['last_zalo_absence_alert_at' => now()]);
            }
            $this->info("Đã gửi danh sách cảnh báo cho " . count($absentList) . " đoàn sinh vào nhóm chat.");
        } else {
            $this->error("Lỗi khi gửi cảnh báo vào nhóm chat.");
        }
    }
}
