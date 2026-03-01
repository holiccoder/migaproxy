<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PostCommentStoreRequest;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PostCommentController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $comments = $post->comments()
            ->with('user')
            ->oldest()
            ->get()
            ->map(function (PostComment $comment): array {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at?->toIso8601String(),
                    'user' => [
                        'id' => $comment->user?->id,
                        'name' => $comment->user?->name,
                        'avatar_url' => $comment->user?->avatar_path ? asset('storage/'.$comment->user->avatar_path) : null,
                    ],
                ];
            })
            ->values();

        return response()->json([
            'data' => $comments,
        ]);
    }

    public function store(PostCommentStoreRequest $request, string $slug): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content' => $request->validated('content'),
        ])->load('user');

        return response()->json([
            'message' => 'Comment posted successfully.',
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->toIso8601String(),
                'user' => [
                    'id' => $comment->user?->id,
                    'name' => $comment->user?->name,
                    'avatar_url' => $comment->user?->avatar_path ? asset('storage/'.$comment->user->avatar_path) : null,
                ],
            ],
        ], 201);
    }
}
