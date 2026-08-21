<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Bulk upload of the phone's offline queue. Rules are intentionally the same
 * shape as RecordBallRequest, just nested under `balls.*`.
 */
class SyncBallsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'balls'                  => ['required', 'array', 'min:1', 'max:300'],
            'balls.*.client_uuid'    => ['required', 'uuid', 'distinct'],
            'balls.*.batsman_id'     => ['required', 'integer', 'exists:players,id'],
            'balls.*.non_striker_id' => ['nullable', 'integer', 'exists:players,id'],
            'balls.*.bowler_id'      => ['required', 'integer', 'exists:players,id'],
            'balls.*.over_number'    => ['required', 'integer', 'min:1', 'max:100'],
            'balls.*.ball_number'    => ['required', 'integer', 'min:1', 'max:6'],
            'balls.*.runs_scored'    => ['required', 'integer', 'min:0', 'max:12'],
            'balls.*.extra_runs'     => ['required', 'integer', 'min:0', 'max:12'],
            'balls.*.is_wide'        => ['boolean'],
            'balls.*.is_no_ball'     => ['boolean'],
            'balls.*.is_bye'         => ['boolean'],
            'balls.*.is_leg_bye'     => ['boolean'],
            'balls.*.is_penalty'     => ['boolean'],
            'balls.*.is_four'        => ['boolean'],
            'balls.*.is_six'         => ['boolean'],
            'balls.*.is_wicket'      => ['boolean'],
            'balls.*.wicket_type'    => ['nullable', Rule::in(RecordBallRequest::WICKET_TYPES)],
            'balls.*.out_player_id'  => ['nullable', 'integer', 'exists:players,id'],
            'balls.*.fielder_id'     => ['nullable', 'integer', 'exists:players,id'],
            'balls.*.commentary'     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'balls.*.client_uuid.required' => 'Every queued ball needs a client_uuid so it can be de-duplicated.',
        ];
    }
}
