<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartInningsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $match = $this->route('match');
        $teams = [$match->home_team_id, $match->away_team_id];

        return [
            'innings_number'  => ['required', 'integer', 'min:1', 'max:4'],
            'batting_team_id' => ['required', 'integer', Rule::in($teams)],
            'bowling_team_id' => ['required', 'integer', Rule::in($teams), 'different:batting_team_id'],
            'target'          => ['nullable', 'integer', 'min:1'],
        ];
    }
}
