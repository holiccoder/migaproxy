<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TicketIndexRequest;
use App\Http\Requests\Api\V1\TicketReplyStoreRequest;
use App\Http\Requests\Api\V1\TicketStoreRequest;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(TicketIndexRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $filters = $request->validated();

        $tickets = $user->tickets()
            ->with(['messages' => fn ($query) => $query->latest()->limit(1)])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }

                    $query->orWhere('subject', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, string $status): void {
                $query->where('status', $status);
            })
            ->when($filters['category'] ?? null, function (Builder $query, string $category): void {
                $query->where('category', $category);
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $dateFrom): void {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $dateTo): void {
                $query->whereDate('created_at', '<=', $dateTo);
            })
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
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('tickets', 'public');
        }

        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'subject' => $payload['subject'],
            'category' => $payload['category'],
            'status' => Ticket::STATUS_OPEN,
            'priority' => $payload['priority'] ?? 'medium',
            'context' => $payload['context'] ?? null,
            'attachment_path' => $attachmentPath,
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
        $attachmentPath = $ticket->attachment_path;

        if ($request->hasFile('attachment')) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $attachmentPath = $request->file('attachment')->store('tickets', 'public');
        }

        $ticket->messages()->create([
            'user_id' => $user->id,
            'admin_id' => null,
            'sender_type' => TicketMessage::SENDER_USER,
            'message' => $payload['message'],
        ]);

        $ticket->forceFill([
            'status' => Ticket::STATUS_OPEN,
            'attachment_path' => $attachmentPath,
            'last_user_reply_at' => now(),
            'resolved_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Reply added successfully.',
            'data' => $ticket->load(['messages' => fn ($query) => $query->oldest()]),
        ]);
    }

    public function close(Request $request, Ticket $ticket): JsonResponse
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
                'message' => 'Ticket is already closed.',
            ]);
        }

        $ticket->forceFill([
            'status' => Ticket::STATUS_CLOSED,
            'resolved_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Ticket closed successfully.',
            'data' => $ticket->load(['messages' => fn ($query) => $query->oldest()]),
        ]);
    }
}
