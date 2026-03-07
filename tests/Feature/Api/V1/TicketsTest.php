<?php

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can create a ticket with initial message', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/tickets', [
        'subject' => 'Payment issue',
        'message' => 'My payment did not go through.',
        'priority' => 'high',
        'category' => 'billing',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Ticket created successfully.')
        ->assertJsonPath('data.subject', 'Payment issue')
        ->assertJsonPath('data.priority', 'high');

    $this->assertDatabaseHas('tickets', [
        'user_id' => $user->id,
        'subject' => 'Payment issue',
        'category' => 'billing',
        'priority' => 'high',
    ]);

    $ticket = Ticket::query()->where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'sender_type' => TicketMessage::SENDER_USER,
    ]);
});

test('user can list only own tickets', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    Ticket::factory()->create(['user_id' => $owner->id, 'subject' => 'Owner ticket']);
    Ticket::factory()->create(['user_id' => $otherUser->id, 'subject' => 'Other ticket']);

    Sanctum::actingAs($owner);

    $response = $this->getJson('/api/v1/tickets');

    $response->assertOk();

    $subjects = collect($response->json('data'))->pluck('subject')->all();

    expect($subjects)
        ->toContain('Owner ticket')
        ->not->toContain('Other ticket');
});

test('user can reply to own ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'status' => Ticket::STATUS_IN_PROGRESS,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/tickets/{$ticket->id}/replies", [
        'message' => 'Any update on this issue?',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Reply added successfully.')
        ->assertJsonPath('data.status', Ticket::STATUS_OPEN);

    $this->assertDatabaseHas('ticket_messages', [
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'sender_type' => TicketMessage::SENDER_USER,
        'message' => 'Any update on this issue?',
    ]);
});

test('user cannot reply to another users ticket', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($otherUser);

    $this->postJson("/api/v1/tickets/{$ticket->id}/replies", [
        'message' => 'Trying to access someone else ticket.',
    ])->assertNotFound();
});
