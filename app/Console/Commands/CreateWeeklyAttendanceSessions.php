<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\TnttClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateWeeklyAttendanceSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:create-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create weekly attendance sessions for all active grade levels for the upcoming Sunday.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weekly attendance session generation...');

        // Get the next Sunday
        $nextSunday = Carbon::now('Asia/Bangkok')->next(Carbon::SUNDAY)->startOfDay();
        $dateStr = $nextSunday->format('d/m/Y');

        // Get all unique classes currently active among Doan Sinh
        $classIds = User::doanSinh()
            ->whereNotNull('tntt_class_id')
            ->distinct()
            ->pluck('tntt_class_id');

        if ($classIds->isEmpty()) {
            $this->info('No active classes found. Exiting.');

            return;
        }

        $sessionsCreated = 0;

        foreach ($classIds as $classId) {
            $tnttClass = TnttClass::find($classId);
            $className = $tnttClass ? $tnttClass->name : 'Unknown Class';

            // Check if session already exists for this date and class
            $exists = AttendanceSession::whereDate('session_date', $nextSunday)
                ->where('tntt_class_id', $classId)
                ->exists();

            if ($exists) {
                $this->warn("Session already exists for {$className} on {$dateStr}. Skipping.");

                continue;
            }

            // Create session
            $session = AttendanceSession::create([
                'title' => "Sinh hoạt CN - {$className} - {$dateStr}",
                'session_date' => $nextSunday,
                'tntt_class_id' => $classId,
                'status' => 'upcoming',
            ]);

            // Get all doan sinh in this class
            $doanSinhs = User::doanSinh()->where('tntt_class_id', $classId)->get();

            // Create default absent records for all of them
            $recordsData = $doanSinhs->map(function ($ds) use ($session) {
                return [
                    'attendance_session_id' => $session->id,
                    'user_id' => $ds->id,
                    'status' => 'absent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (! empty($recordsData)) {
                AttendanceRecord::insert($recordsData);
            }

            $sessionsCreated++;
            $this->info("Created session for {$className} with {$doanSinhs->count()} records.");
        }

        $this->info("Successfully created {$sessionsCreated} attendance sessions for {$dateStr}.");
    }
}
