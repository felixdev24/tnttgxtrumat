<?php

use App\Models\User;
use App\Models\CalendarEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access dashboard calendar', function () {
    $response = $this->get(route('dashboard.calendar.index'));

    $response->assertRedirect('/login');
});

test('non-huynh_truong user cannot access dashboard calendar', function () {
    $user = User::factory()->create([
        'role' => 'giao_ly_sinh',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.calendar.index'));

    $response->assertRedirect('/');
});

test('huynh_truong can view dashboard calendar', function () {
    $user = User::factory()->create([
        'role' => 'huynh_truong',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.calendar.index'));

    $response->assertOk();
});

test('huynh_truong can create a calendar event', function () {
    $user = User::factory()->create([
        'role' => 'huynh_truong',
    ]);

    $response = $this->actingAs($user)->post(route('dashboard.calendar.store'), [
        'event_date' => '2026-06-09',
        'title' => 'Lễ Trọng Mới',
        'type' => 'solemnity',
        'description' => 'Mô tả lễ trọng mới',
    ]);

    $response->assertRedirect(route('dashboard.calendar.index'));
    $this->assertDatabaseHas('calendar_events', [
        'title' => 'Lễ Trọng Mới',
        'type' => 'solemnity',
        'event_date' => '2026-06-09',
    ]);
});

test('huynh_truong can update a calendar event', function () {
    $user = User::factory()->create([
        'role' => 'huynh_truong',
    ]);

    $event = CalendarEvent::create([
        'event_date' => '2026-06-09',
        'title' => 'Lễ Cũ',
        'type' => 'memorial',
        'description' => 'Mô tả lễ cũ',
    ]);

    $response = $this->actingAs($user)->put(route('dashboard.calendar.update', $event), [
        'event_date' => '2026-06-10',
        'title' => 'Lễ Mới Cập Nhật',
        'type' => 'solemnity',
        'description' => 'Mô tả lễ mới cập nhật',
    ]);

    $response->assertRedirect(route('dashboard.calendar.index'));
    $this->assertDatabaseHas('calendar_events', [
        'id' => $event->id,
        'title' => 'Lễ Mới Cập Nhật',
        'type' => 'solemnity',
        'event_date' => '2026-06-10',
    ]);
});

test('huynh_truong can delete a calendar event', function () {
    $user = User::factory()->create([
        'role' => 'huynh_truong',
    ]);

    $event = CalendarEvent::create([
        'event_date' => '2026-06-09',
        'title' => 'Lễ Xóa',
        'type' => 'note',
        'description' => 'Sắp bị xóa',
    ]);

    $response = $this->actingAs($user)->delete(route('dashboard.calendar.destroy', $event));

    $response->assertRedirect(route('dashboard.calendar.index'));
    $this->assertDatabaseMissing('calendar_events', [
        'id' => $event->id,
    ]);
});
