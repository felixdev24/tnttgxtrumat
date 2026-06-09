<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published'],
            'category' => ['nullable', 'string', 'max:255', Rule::exists('post_categories', 'name')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề bài viết không được để trống.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'cover_image.image' => 'Ảnh bìa phải là file hình ảnh.',
            'cover_image.max' => 'Ảnh bìa không được vượt quá 5MB.',
            'scheduled_at.after' => 'Thời gian hẹn đăng phải sau thời điểm hiện tại.',
        ];
    }
}
