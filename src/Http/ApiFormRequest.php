<?php

namespace Teksite\Extralaravel\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiFormRequest extends FormRequest
{

    public function failedValidation(Validator $validator)
    {
        return throw new HttpResponseException(response()->json([
            'messages' => $validator->errors(),
            'status' => 422,
            'data' => [],
        ])->setStatusCode(422));
    }


    public function failedAuthorization()
    {
        return throw new HttpResponseException(response()->json([
            'messages' => ["Forbidden You don't have permission"],
            'status' => 403,
            'data' => [],
        ])->setStatusCode(403));
    }
}
