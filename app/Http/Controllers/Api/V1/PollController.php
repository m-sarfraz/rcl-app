<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PollResource;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * Cast a vote.
     *
     * `poll_votes` has carried `ip_address` and `session_token` columns from
     * day one and nothing ever wrote them, so the ballot could be stuffed in a
     * loop. One vote per device token, falling back to IP.
     */
    public function vote(Request $request, Poll $poll): JsonResponse
    {
        $validated = $request->validate([
            'option_id'    => 'required|integer|exists:poll_options,id',
            'device_token' => 'nullable|string|max:100',
        ]);

        if (! $poll->is_active || ($poll->ends_at && $poll->ends_at->isPast())) {
            return ApiResponse::error('This poll is closed.', 422, [], 'poll_closed');
        }

        $option = PollOption::findOrFail($validated['option_id']);

        if ((int) $option->poll_id !== (int) $poll->id) {
            return ApiResponse::error('That option does not belong to this poll.', 422, [], 'invalid_option');
        }

        $token = $validated['device_token'] ?? null;
        $ip    = $request->ip();

        $already = PollVote::where('poll_id', $poll->id)
            ->when($token, fn ($q) => $q->where('session_token', $token), fn ($q) => $q->where('ip_address', $ip))
            ->first();

        if ($already) {
            return ApiResponse::error(
                'You have already voted in this poll.',
                409,
                ['voted_option_id' => $already->poll_option_id],
                'already_voted'
            );
        }

        PollVote::create([
            'poll_id'        => $poll->id,
            'poll_option_id' => $option->id,
            'user_id'        => $request->user()?->id,
            'ip_address'     => $ip,
            'session_token'  => $token,
        ]);

        $poll->load(['options' => fn ($q) => $q->withCount('votes')->orderBy('display_order')]);

        return ApiResponse::success(
            new PollResource($poll),
            'Vote recorded.'
        );
    }

    public function show(Poll $poll): JsonResponse
    {
        $poll->load(['options' => fn ($q) => $q->withCount('votes')->orderBy('display_order')]);

        return ApiResponse::success(new PollResource($poll));
    }
}
