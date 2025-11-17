<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequest extends FormRequest
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
            'clock_in' => [
                'nullable',
                'before:clock_out',
                'required_with:clock_out',
                'required_with:break_start.*',
                'required_with:break_end.*',
            ],
            'break_start.*' => [
                'nullable',
                'after:clock_in',
                'before:clock_out',
                'before:break_end.*',
                'required_with:break_end.*',
            ],
            'break_end.*' => [
                'nullable',
                'before:clock_out',
                'required_with:break_start.*',
            ],
            'clock_out' => [
                'nullable',
                'required_with:clock_in',
            ],
            'comment' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.required_with' => '出勤時間を入力してください',

            'break_start.*.after' => '休憩時間が不適切な値です',
            'break_start.*.before' => '休憩時間が不適切な値です',
            'break_start.*.required_with' => '休憩開始時間を入力してください',

            'break_end.*.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_end.*.required_with' => '休憩終了時間を入力してください',

            'clock_out.required_with' => '退勤時間を入力してください',

            'comment.required' => '備考を記入してください',
        ];
    }
}
