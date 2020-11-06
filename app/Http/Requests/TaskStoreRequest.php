<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'owner_id' => ['present', 'nullable', Rule::exists(User::class, 'id')],
            'reporter_id' => ['required', Rule::exists(User::class, 'id')],
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
        ];
    }
}
