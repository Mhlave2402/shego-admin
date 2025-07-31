<?php

namespace Modules\PromotionManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SendNotificationStoreUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:101',
            'description' => 'nullable|string|max:201',
            'targeted_users' => [
                'required',
                'array',
                Rule::in(['customers', 'drivers'])
            ],
            'image' => [
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048']
        ];
    }

    public function authorize()
    {
        return Auth::check();
    }
}
