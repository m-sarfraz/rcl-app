<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One delivery, as reported by the phone's scoring engine.
 *
 * The rules mirror the engine's own invariants so a bug on the client cannot
 * quietly write nonsense into the ball log.
 */
class RecordBallRequest extends FormRequest
{
    public const WICKET_TYPES = [
        'bowled', 'caught', 'run_out', 'lbw', 'stumped', 'hit_wicket',
        'obstructing_field', 'handled_ball', 'timed_out', 'retired_hurt',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_uuid'    => ['nullable', 'uuid'],
            'batsman_id'     => ['required', 'integer', 'exists:players,id'],
            'non_striker_id' => ['nullable', 'integer', 'exists:players,id', 'different:batsman_id'],
            'bowler_id'      => ['required', 'integer', 'exists:players,id'],

            'over_number'    => ['nullable', 'integer', 'min:1', 'max:100'],
            'ball_number'    => ['nullable', 'integer', 'min:1', 'max:6'],

            'runs_scored'    => ['required', 'integer', 'min:0', 'max:12'],
            'extra_runs'     => ['required', 'integer', 'min:0', 'max:12'],

            'is_wide'        => ['boolean'],
            'is_no_ball'     => ['boolean'],
            'is_bye'         => ['boolean'],
            'is_leg_bye'     => ['boolean'],
            'is_penalty'     => ['boolean'],
            'is_four'        => ['boolean'],
            'is_six'         => ['boolean'],

            'is_wicket'      => ['boolean'],
            'wicket_type'    => ['nullable', 'required_if:is_wicket,true', Rule::in(self::WICKET_TYPES)],
            'out_player_id'  => ['nullable', 'integer', 'exists:players,id'],
            'fielder_id'     => ['nullable', 'integer', 'exists:players,id'],

            'score_after'    => ['nullable', 'integer', 'min:0'],
            'wickets_after'  => ['nullable', 'integer', 'min:0'],
            'commentary'     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $d = $this->all();

            if (($d['is_wide'] ?? false) && ($d['is_no_ball'] ?? false)) {
                $v->errors()->add('is_wide', 'A delivery cannot be both a wide and a no-ball.');
            }

            if (($d['is_bye'] ?? false) && ($d['is_leg_bye'] ?? false)) {
                $v->errors()->add('is_bye', 'A delivery cannot be both a bye and a leg-bye.');
            }

            if (($d['is_wide'] ?? false) && (int) ($d['extra_runs'] ?? 0) < 1) {
                $v->errors()->add('extra_runs', 'A wide is worth at least one extra run.');
            }

            if (($d['is_no_ball'] ?? false) && (int) ($d['extra_runs'] ?? 0) < 1) {
                $v->errors()->add('extra_runs', 'A no-ball is worth at least one extra run.');
            }

            if ((($d['is_bye'] ?? false) || ($d['is_leg_bye'] ?? false)) && (int) ($d['runs_scored'] ?? 0) > 0) {
                $v->errors()->add('runs_scored', 'Byes and leg-byes are extras, not runs off the bat.');
            }

            if ($d['is_penalty'] ?? false) {
                if ((int) ($d['extra_runs'] ?? 0) < 1) {
                    $v->errors()->add('extra_runs', 'A penalty award is worth at least one run.');
                }
                if ((int) ($d['runs_scored'] ?? 0) > 0) {
                    $v->errors()->add('runs_scored', 'A penalty is an award, not runs off the bat.');
                }
                if (($d['is_wide'] ?? false) || ($d['is_no_ball'] ?? false)) {
                    $v->errors()->add('is_penalty', 'A penalty award is recorded on its own, not alongside another extra.');
                }
                if ($d['is_wicket'] ?? false) {
                    $v->errors()->add('is_wicket', 'No delivery is bowled for a penalty, so no wicket can fall on it.');
                }
            }

            $caughtLike = in_array($d['wicket_type'] ?? '', ['caught', 'stumped'], true);
            if (($d['is_wicket'] ?? false) && $caughtLike && empty($d['fielder_id'])) {
                $v->errors()->add('fielder_id', 'Name the fielder who took the catch or stumping.');
            }

            if (($d['is_wicket'] ?? false) && ($d['wicket_type'] ?? '') === 'stumped' && ($d['is_no_ball'] ?? false)) {
                $v->errors()->add('wicket_type', 'A batter cannot be stumped off a no-ball.');
            }

            if (($d['is_wicket'] ?? false) && ($d['is_wide'] ?? false)
                && ! in_array($d['wicket_type'] ?? '', ['run_out', 'stumped', 'hit_wicket', 'obstructing_field'], true)) {
                $v->errors()->add('wicket_type', 'Only a run-out, stumping, hit wicket or obstruction can happen off a wide.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        // Accept the older `comment` key without breaking anything already sending it.
        if ($this->has('comment') && ! $this->has('commentary')) {
            $this->merge(['commentary' => $this->input('comment')]);
        }
    }
}
