<?php

use App\Models\TnttClass;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $this->huynhTruong = User::factory()->create([
        'role' => 'huynh_truong',
    ]);
});

it('allows huynh truong to view doan sinh list', function () {
    User::factory()->count(3)->doanSinh()->create();

    actingAs($this->huynhTruong)
        ->get('/dashboard/doan-sinh')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('dashboard/doan-sinh/Index')
            ->has('doanSinhs.data', 3)
        );
});

it('allows huynh truong to create doan sinh', function () {
    $class = TnttClass::factory()->create();

    $data = [
        'name' => 'Nguyen Van A',
        'username' => 'nguyenvana',
        'tntt_class_id' => $class->id,
        'branch' => 'Ấu',
        'dob' => '2015-01-01',
    ];

    actingAs($this->huynhTruong)
        ->post('/dashboard/doan-sinh', $data)
        ->assertRedirect('/dashboard/doan-sinh');

    assertDatabaseHas('users', [
        'username' => 'nguyenvana',
        'role' => 'giao_ly_sinh',
        'tntt_class_id' => $class->id,
    ]);
});

it('auto generates qr_token for new doan sinh', function () {
    $class = TnttClass::factory()->create();

    $data = [
        'name' => 'Nguyen Van B',
        'username' => 'nguyenvanb',
        'tntt_class_id' => $class->id,
        'branch' => 'Ấu',
    ];

    actingAs($this->huynhTruong)
        ->post('/dashboard/doan-sinh', $data);

    $user = User::where('username', 'nguyenvanb')->first();
    expect($user->qr_token)->not->toBeNull();
});

it('allows huynh truong to update doan sinh', function () {
    $doanSinh = User::factory()->doanSinh()->create([
        'name' => 'Old Name',
    ]);

    $newClass = TnttClass::factory()->create();

    actingAs($this->huynhTruong)
        ->put("/dashboard/doan-sinh/{$doanSinh->id}", [
            'name' => 'New Name',
            'username' => $doanSinh->username,
            'tntt_class_id' => $newClass->id,
            'branch' => 'Ấu',
        ])
        ->assertRedirect();

    assertDatabaseHas('users', [
        'id' => $doanSinh->id,
        'name' => 'New Name',
        'tntt_class_id' => $newClass->id,
    ]);
});

it('allows huynh truong to delete doan sinh', function () {
    $doanSinh = User::factory()->doanSinh()->create();

    actingAs($this->huynhTruong)
        ->delete("/dashboard/doan-sinh/{$doanSinh->id}")
        ->assertRedirect('/dashboard/doan-sinh');

    assertDatabaseMissing('users', [
        'id' => $doanSinh->id,
    ]);
});

it('can generate qr code for doan sinh', function () {
    $doanSinh = User::factory()->doanSinh()->create();

    actingAs($this->huynhTruong)
        ->get("/dashboard/doan-sinh/{$doanSinh->id}/qr")
        ->assertStatus(200)
        ->assertJsonStructure(['svg', 'token', 'name']);
});
