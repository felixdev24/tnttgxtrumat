<?php

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->huynhTruong = User::factory()->create([
        'role' => 'huynh_truong',
    ]);
});

it('allows huynh truong to create attendance session', function () {
    User::factory()->count(3)->doanSinh()->create(['grade_level' => 'Rước Lễ 1']);

    actingAs($this->huynhTruong)
        ->post('/dashboard/attendance', [
            'title' => 'Sinh hoạt Rước Lễ 1',
            'session_date' => now()->format('Y-m-d'),
            'grade_level' => 'Rước Lễ 1',
        ])
        ->assertRedirect('/dashboard/attendance');

    $session = AttendanceSession::where('title', 'Sinh hoạt Rước Lễ 1')->first();
    expect($session)->not->toBeNull();

    // Auto created records
    expect(AttendanceRecord::where('attendance_session_id', $session->id)->count())->toBe(3);
});

it('allows check in via qr token', function () {
    $session = AttendanceSession::factory()->create(['grade_level' => 'Rước Lễ 1']);
    $doanSinh = User::factory()->doanSinh()->create(['grade_level' => 'Rước Lễ 1']);

    // Initialize absent record
    AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'user_id' => $doanSinh->id,
        'status' => 'absent',
    ]);

    actingAs($this->huynhTruong)
        ->postJson('/dashboard/attendance/check-in', [
            'session_id' => $session->id,
            'qr_token' => $doanSinh->qr_token,
        ])
        ->assertStatus(200)
        ->assertJson(['success' => true]);

    assertDatabaseHas('attendance_records', [
        'attendance_session_id' => $session->id,
        'user_id' => $doanSinh->id,
        'status' => 'present',
    ]);
});

it('prevents check in for wrong grade level', function () {
    $session = AttendanceSession::factory()->create(['grade_level' => 'Rước Lễ 1']);
    $doanSinh = User::factory()->doanSinh()->create(['grade_level' => 'Khai Tâm 1']);

    actingAs($this->huynhTruong)
        ->postJson('/dashboard/attendance/check-in', [
            'session_id' => $session->id,
            'qr_token' => $doanSinh->qr_token,
        ])
        ->assertStatus(403);
});

it('allows manual check in status update', function () {
    $session = AttendanceSession::factory()->create();
    $doanSinh = User::factory()->doanSinh()->create();
    $record = AttendanceRecord::factory()->create([
        'attendance_session_id' => $session->id,
        'user_id' => $doanSinh->id,
        'status' => 'absent',
    ]);

    actingAs($this->huynhTruong)
        ->post("/dashboard/attendance/{$session->id}/manual", [
            'record_id' => $record->id,
            'status' => 'excused',
        ])
        ->assertRedirect();

    assertDatabaseHas('attendance_records', [
        'id' => $record->id,
        'status' => 'excused',
    ]);
});

it('scheduled command creates weekly sessions for next sunday', function () {
    Carbon::setTestNow('2026-06-25 10:00:00'); // Assuming a Thursday
    $nextSunday = Carbon::parse('2026-06-28 00:00:00');

    // Setup active grade levels
    User::factory()->doanSinh()->create(['grade_level' => 'Bao Đồng 1']);
    User::factory()->doanSinh()->create(['grade_level' => 'Bao Đồng 2']);

    Artisan::call('attendance:create-weekly');

    assertDatabaseHas('attendance_sessions', [
        'session_date' => $nextSunday->format('Y-m-d'),
        'grade_level' => 'Bao Đồng 1',
    ]);

    assertDatabaseHas('attendance_sessions', [
        'session_date' => $nextSunday->format('Y-m-d'),
        'grade_level' => 'Bao Đồng 2',
    ]);

    Carbon::setTestNow(); // Reset
});
