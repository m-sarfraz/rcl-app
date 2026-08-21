<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'man_of_match_player_id' => ['nullable', 'integer', 'exists:players,id'],
            'result_description'     => ['nullable', 'string', 'max:500'],
            'notes'                  => ['nullable', 'string', 'max:2000'],
            'finalized_by'           => ['nullable', 'string', 'max:60'],
        ];
    }
}
