<?php

use App\Actions\Notifications\SendBulkPrivateMessage;
use App\Models\Admin;
use App\Models\User;

test('bulk private message sends database notification to all users', function () {
    $admin = Admin::factory()->create([
        'name' => 'Admin Sender',
    ]);
    User::factory()->count(3)->create();

    $sentCount = app(SendBulkPrivateMessage::class)->execute(
        sender: $admin,
        subject: 'Maintenance Window',
        message: 'The app will be briefly unavailable tonight.',
    );

    expect($sentCount)->toBe(3);
    expect(\Illuminate\Support\Facades\DB::table('notifications')->count())->toBe(3);

    $firstUser = User::query()->firstOrFail();
    $notification = $firstUser->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification?->data['subject'])->toBe('Maintenance Window');
    expect($notification?->data['admin_name'])->toBe('Admin Sender');
});
