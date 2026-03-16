<?php

use App\Actions\Notifications\SendBulkPrivateMessage;
use App\Actions\Notifications\SendBulkPrivateMessageJob;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\AdminPrivateMessageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

test('bulk private message dispatches a queued job and returns recipient count', function () {
    Queue::fake();

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
    expect(DB::table('notifications')->count())->toBe(0);

    Queue::assertPushed(SendBulkPrivateMessageJob::class, function (SendBulkPrivateMessageJob $job) use ($admin): bool {
        return $job->adminId === $admin->id
            && $job->adminName === 'Admin Sender'
            && $job->subject === 'Maintenance Window'
            && $job->message === 'The app will be briefly unavailable tonight.'
            && $job->sendToEmail === false;
    });
});

test('bulk private message dispatches email-enabled queued job when requested', function () {
    Queue::fake();

    $admin = Admin::factory()->create([
        'name' => 'Admin Sender',
    ]);
    User::factory()->count(2)->create();

    $sentCount = app(SendBulkPrivateMessage::class)->execute(
        sender: $admin,
        subject: 'Maintenance Window',
        message: 'The app will be briefly unavailable tonight.',
        sendToEmail: true,
    );

    expect($sentCount)->toBe(2);

    Queue::assertPushed(SendBulkPrivateMessageJob::class, function (SendBulkPrivateMessageJob $job): bool {
        return $job->sendToEmail === true;
    });
});

test('bulk private message queued job sends database notifications', function () {
    $admin = Admin::factory()->create([
        'name' => 'Admin Sender',
    ]);
    User::factory()->count(3)->create();

    $job = new SendBulkPrivateMessageJob(
        adminId: $admin->id,
        adminName: $admin->name,
        subject: 'Maintenance Window',
        message: 'The app will be briefly unavailable tonight.',
        sendToEmail: false,
    );

    $job->handle();

    expect(DB::table('notifications')->count())->toBe(3);

    $firstUser = User::query()->firstOrFail();
    $notification = $firstUser->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification?->data['subject'])->toBe('Maintenance Window');
    expect($notification?->data['admin_name'])->toBe('Admin Sender');
});

test('bulk private message queued job sends mail channel when enabled', function () {
    Notification::fake();

    $admin = Admin::factory()->create([
        'name' => 'Admin Sender',
    ]);
    $users = User::factory()->count(2)->create();

    $job = new SendBulkPrivateMessageJob(
        adminId: $admin->id,
        adminName: $admin->name,
        subject: 'Maintenance Window',
        message: 'The app will be briefly unavailable tonight.',
        sendToEmail: true,
    );

    $job->handle();

    foreach ($users as $user) {
        Notification::assertSentTo(
            $user,
            AdminPrivateMessageNotification::class,
            function (AdminPrivateMessageNotification $notification, array $channels) use ($admin): bool {
                return $notification->subject === 'Maintenance Window'
                    && $notification->adminId === $admin->id
                    && in_array('database', $channels, true)
                    && in_array('mail', $channels, true);
            }
        );
    }
});
