<?php

namespace LaravelEnso\Products\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateMeasurementUnitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            //
        ];
    }
}