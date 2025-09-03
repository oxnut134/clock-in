<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MyDetailRequest extends FormRequest
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
        $rules = [
            'job_start' => 'required|date_format:H:i',
            'job_finish' => 'nullable|date_format:H:i',
            'remark' => 'required|string|max:100',

        ];
        //if ($this->input('job_finish')) {
        //    $rules['job_start'] .= '|before:job_finish';
        //}
        $this->input('job_finish') ? $rules['job_start'] .= '|before:job_finish' : null;
        $this->input('job_finish') ? $rules['job_finish'] .= '|after:job_start' : null;
        // $breakTimesがnullの場合は空配列に設定
        $breakTimes = $this->input('breakTimes', []);

        foreach ($breakTimes as $index => $breakTime) {
            $rules["breakTimes.$index.break_start"] = 'nullable|date_format:H:i|after:job_start';
            $rules["breakTimes.$index.break_finish"] = 'nullable|date_format:H:i|after:breakTimes.' . $index . '.break_start';

            if ($this->input('job_finish')) {
                $rules["breakTimes.$index.break_start"] .= '|before:job_finish';
                $rules["breakTimes.$index.break_finish"] .= '|before:job_finish';
            }
        }



        return $rules;
    }
    public function messages()
    {
        $messages = [
            'job_start.required' => '出勤時間を入力してください',
            'job_start.date_format' => '出勤時間が不適切な値です',
            'job_start.before' => '出勤時間が不適切な時刻です',
            //'job_start.before' => '出勤時間もしくは退勤時間が不適切な時刻です',
            'job_finish.date_format' => '退勤時間が不適切な値です',
            'job_finish.after' => '出勤時間もしくは退勤時間が不適切な時刻です',
            'remark.required' => '備考を記入してください',
            'remark.max' => '備考を100字以内で記入してください',
        ];

        foreach ($this->input('breakTimes', []) as $index => $breakTime) {
            $messages["breakTimes.$index.break_start.date_format"] = "休憩時間が不適切な値です";
            $messages["breakTimes.$index.break_finish.date_format"] = "休憩時間が不適切な値です";
            $messages["breakTimes.$index.break_start.before"] = "休憩時間が不適切な時刻です";
            $messages["breakTimes.$index.break_start.after"] = "休憩時間が不適切な時刻です";
            $messages["breakTimes.$index.break_finish.before"] = "休憩時間もしくは退勤時間が不適切な時刻です";
            $messages["breakTimes.$index.break_finish.after"] = "休憩時間か不適切な時刻です";
        }

        return $messages;
    }
}
