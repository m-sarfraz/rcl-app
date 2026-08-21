<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function index()
    {
        // Votes are rows in `poll_votes`, not a counter column on the option,
        // so the total has to be counted rather than summed off the model.
        $polls = Poll::with(['edition', 'options' => fn ($q) => $q->withCount('votes')])
            ->latest()
            ->paginate(15);

        return view('admin.polls.index', compact('polls'));
    }

    public function create()
    {
        $editions = Edition::orderByDesc('edition_number')->get();
        return view('admin.polls.create', compact('editions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'    => 'required|string|max:500',
            'edition_id'  => 'required|exists:editions,id',
            'match_id'    => 'nullable|exists:cricket_matches,id',
            'ends_at'     => 'nullable|date|after:now',
            'is_active'   => 'boolean',
            'options'     => 'required|array|min:2|max:6',
            'options.*'   => 'required|string|max:255',
        ]);

        $poll = Poll::create($data);

        foreach ($request->options as $text) {
            PollOption::create(['poll_id' => $poll->id, 'option_text' => $text]);
        }

        return redirect()->route('admin.polls.index')->with('success', 'Poll created.');
    }

    public function toggle(Poll $poll)
    {
        $poll->update(['is_active' => !$poll->is_active]);
        return back()->with('success', 'Poll status toggled.');
    }

    public function destroy(Poll $poll)
    {
        $poll->delete();
        return back()->with('success', 'Poll deleted.');
    }
}
