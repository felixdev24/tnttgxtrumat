<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
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

        // Get all unique grade levels currently active among Doan Sinh
        $gradeLevels = User::doanSinh()
            ->whereNotNull('grade_level')
            ->distinct()
            ->pluck('grade_level');

        if ($gradeLevels->isEmpty()) {
            $this->info('No active grade levels found. Exiting.');

            return;
        }

        $sessionsCreated = 0;

        foreach ($gradeLevels as $gradeLevel) {
            // Check if session already exists for this date and grade level
            $exists = AttendanceSession::whereDate('session_date', $nextSunday)
                ->where('grade_level', $gradeLevel)
                ->exists();

            if ($exists) {
                $this->warn("Session already exists for {$gradeLevel} on {$dateStr}. Skipping.");

                continue;
            }

            // Create session
            $session = AttendanceSession::create([
                'title' => "Sinh hoạt CN - {$gradeLevel} - {$dateStr}",
                'session_date' => $nextSunday,
                'grade_level' => $gradeLevel,
                'status' => 'upcoming',
            ]);

            // Get all doan sinh in this grade level
            $doanSinhs = User::doanSinh()->where('grade_level', $gradeLevel)->get();

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
            $this->info("Created session for {$gradeLevel} with {$doanSinhs->count()} records.");
        }

        $this->info("Successfully created {$sessionsCreated} attendance sessions for {$dateStr}.");
    }
}
