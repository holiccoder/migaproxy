<?php

use App\Models\Ticket;
use App\Models\TicketMessage;
use Carbon\Carbon;

test('command closes only stale open or in progress tickets', function () {
    $now = now()->startOfSecond();
    Carbon::setTestNow($now);

    $staleOpenTicket = Ticket::factory()->create([
        'status' => Ticket::STATUS_OPEN,
        'resolved_at' => null,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $staleOpenTicket->id,
        'created_at' => $now->copy()->subDays(4),
        'updated_at' => $now->copy()->subDays(4),
    ]);

    $staleInProgressTicket = Ticket::factory()->create([
        'status' => Ticket::STATUS_IN_PROGRESS,
        'resolved_at' => null,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $staleInProgressTicket->id,
        'created_at' => $now->copy()->subDays(5),
        'updated_at' => $now->copy()->subDays(5),
    ]);

    $recentTicket = Ticket::factory()->create([
        'status' => Ticket::STATUS_OPEN,
        'resolved_at' => null,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $recentTicket->id,
        'created_at' => $now->copy()->subDay(),
        'updated_at' => $now->copy()->subDay(),
    ]);

    $alreadyClosedTicket = Ticket::factory()->create([
        'status' => Ticket::STATUS_CLOSED,
        'resolved_at' => null,
    ]);

    TicketMessage::factory()->create([
        'ticket_id' => $alreadyClosedTicket->id,
        'created_at' => $now->copy()->subDays(10),
        'updated_at' => $now->copy()->subDays(10),
    ]);

    $this->artisan('tickets:close-stale')
        ->expectsOutput('Closed 2 stale ticket(s).')
        ->assertSuccessful();

    $staleOpenTicket->refresh();
    $staleInProgressTicket->refresh();
    $recentTicket->refresh();
    $alreadyClosedTicket->refresh();

    expect($staleOpenTicket->status)->toBe(Ticket::STATUS_CLOSED);
    expect($staleOpenTicket->resolved_at?->equalTo($now))->toBeTrue();
    expect($staleInProgressTicket->status)->toBe(Ticket::STATUS_CLOSED);
    expect($staleInProgressTicket->resolved_at?->equalTo($now))->toBeTrue();
    expect($recentTicket->status)->toBe(Ticket::STATUS_OPEN);
    expect($recentTicket->resolved_at)->toBeNull();
    expect($alreadyClosedTicket->status)->toBe(Ticket::STATUS_CLOSED);

    Carbon::setTestNow();
});
