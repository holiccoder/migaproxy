<?php

use App\Models\Admin;
use App\Models\User;
use App\Notifications\AdminPrivateMessageNotification;
use Laravel\Sanctum\Sanctum;

test('user can list private notifications', function () {
    $user = User::factory()->create();
    $admin = Admin::factory()->create();

    $user->notify(new AdminPrivateMessageNotification(
        subject: 'Welcome',
        message: 'Your account is approved.',
        adminId: $admin->id,
        adminName: $admin->name,
    ));

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/notifications');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.data.subject', 'Welcome')
        ->assertJsonPath('data.0.data.message', 'Your account is approved.')
        ->assertJsonPath('data.0.data.admin_id', $admin->id);
});

test('user cannot see another users notifications', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $owner->notify(new AdminPrivateMessageNotification(
        subject: 'Private',
        message: 'Only owner can see this.',
    ));

    Sanctum::actingAs($otherUser);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertOk();
    expect($response->json('data'))->toBeArray()->toHaveCount(0);
});

test('user can mark own notification as read', function () {
    $user = User::factory()->create();

    $user->notify(new AdminPrivateMessageNotification(
        subject: 'Notice',
        message: 'Read this message.',
    ));

    $notificationId = (string) $user->notifications()->value('id');

    Sanctum::actingAs($user);

    $this->postJson("/api/v1/notifications/{$notificationId}/read")
        ->assertOk()
        ->assertJsonPath('message', 'Notification marked as read.');

    expect($user->notifications()->first()?->read_at)->not->toBeNull();
});

test('user cannot mark another users notification as read', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $owner->notify(new AdminPrivateMessageNotification(
        subject: 'Owner only',
        message: 'Do not read.',
    ));

    $notificationId = (string) $owner->notifications()->value('id');

    Sanctum::actingAs($otherUser);

    $this->postJson("/api/v1/notifications/{$notificationId}/read")
        ->assertNotFound()
        ->assertJsonPath('message', 'Notification not found.');
});
