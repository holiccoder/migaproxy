<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TicketReplyStoreRequest;
use App\Http\Requests\Api\V1\TicketStoreRequest;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $tickets = $user->tickets()
            ->with(['messages' => fn ($query) => $query->latest()->limit(1)])
            ->latest()
            ->paginate(15);

        return response()->json($tickets);
    }

    public function store(TicketStoreRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $payload = $request->validated();

        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'subject' => $payload['subject'],
            'status' => Ticket::STATUS_OPEN,
            'priority' => $payload['priority'] ?? 'medium',
            'last_user_reply_at' => now(),
            'last_admin_reply_at' => null,
            'resolved_at' => null,
        ]);

        $ticket->messages()->create([
            'user_id' => $user->id,
            'admin_id' => null,
            'sender_type' => TicketMessage::SENDER_USER,
            'message' => $payload['message'],
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => $ticket->load('messages'),
        ], 201);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        return response()->json([
            'data' => $ticket->load(['messages' => fn ($query) => $query->oldest()]),
        ]);
    }

    public function reply(TicketReplyStoreRequest $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], 404);
        }

        if ($ticket->status === Ticket::STATUS_CLOSED) {
            return response()->json([
                'message' => 'Closed tickets cannot be updated.',
            ], 422);
        }

        $payload = $request->validated();

        $ticket->messages()->create([
            'user_id' => $user->id,
            'admin_id' => null,
            'sender_type' => TicketMessage::SENDER_USER,
            'message' => $payload['message'],
        ]);

        $ticket->forceFill([
            'status' => Ticket::STATUS_OPEN,
            'last_user_reply_at' => now(),
            'resolved_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Reply added successfully.',
            'data' => $ticket->load(['messages' => fn ($query) => $query->oldest()]),
        ]);
    }
}
