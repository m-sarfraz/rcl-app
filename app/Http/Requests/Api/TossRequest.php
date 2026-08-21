<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TossRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $match = $this->route('match');

        return [
            'toss_winner_id' => ['required', 'integer', Rule::in([$match->home_team_id, $match->away_team_id])],
            'decision'       => ['required', Rule::in(['bat', 'field'])],
        ];
    }

    public function messages(): array
    {
        return [
            'toss_winner_id.in' => 'The toss winner must be one of the two teams in this match.',
        ];
    }
}
