<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Có thể thêm logic kiểm tra permission admin
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    if ($this->discount_type === 'percentage' && $value > 100) {
                        $fail('Phần trăm giảm giá không được vượt quá 100%.');
                    }
                }
            ],
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên khuyến mãi là bắt buộc.',
            'name.max' => 'Tên khuyến mãi không được vượt quá 255 ký tự.',
            'discount_type.required' => 'Loại giảm giá là bắt buộc.',
            'discount_type.in' => 'Loại giảm giá không hợp lệ.',
            'discount_value.required' => 'Giá trị giảm giá là bắt buộc.',
            'discount_value.min' => 'Giá trị giảm giá phải lớn hơn 0.',
            'product_id.required' => 'Sản phẩm là bắt buộc.',
            'product_id.exists' => 'Sản phẩm được chọn không tồn tại.',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_date.required' => 'Ngày kết thúc là bắt buộc.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active')
        ]);
    }
}
