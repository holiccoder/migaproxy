<?php

use App\Actions\IPmart\CheckUserIpmartAvailableTrafficJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('command queues ipmart available traffic checks for all users', function () {
    Queue::fake();

    $users = User::factory()->count(3)->create();

    $this->artisan('ipmart:queue-available-traffic-checks')
        ->expectsOutput('Dispatched 3 IPmart available traffic check job(s).')
        ->assertSuccessful();

    Queue::assertPushed(CheckUserIpmartAvailableTrafficJob::class, 3);

    foreach ($users as $user) {
        Queue::assertPushed(CheckUserIpmartAvailableTrafficJob::class, function (CheckUserIpmartAvailableTrafficJob $job) use ($user): bool {
            return $job->userId === $user->id;
        });
    }
});
