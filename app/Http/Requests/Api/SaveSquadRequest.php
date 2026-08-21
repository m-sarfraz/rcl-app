<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSquadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $match = $this->route('match');

        return [
            'team_id'                    => ['required', 'integer', Rule::in([$match->home_team_id, $match->away_team_id])],
            'players'                    => ['required', 'array', 'min:2', 'max:20'],
            'players.*.player_id'        => ['required', 'integer', 'distinct', 'exists:players,id'],
            'players.*.batting_order'    => ['nullable', 'integer', 'min:1', 'max:20'],
            'players.*.is_captain'       => ['nullable', 'boolean'],
            'players.*.is_wicket_keeper' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'players.min'                => 'A playing side needs at least two players.',
            'players.*.player_id.distinct'=> 'The same player cannot be named twice in one XI.',
        ];
    }
}
