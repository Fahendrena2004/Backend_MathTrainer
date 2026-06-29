<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'exercise_id' => ['required', 'integer', Rule::exists('exercises', 'id')],
            'learning_session_id' => ['nullable', 'integer', Rule::exists('learning_sessions', 'id')],
            'answer' => ['required', 'string'],
            'time_spent' => ['nullable', 'integer', 'min:0'],
            'file_url' => ['nullable', 'string'],
        ];
    }
}
