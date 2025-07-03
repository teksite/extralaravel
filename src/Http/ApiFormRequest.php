<?php

namespace Teksite\Extralaravel\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Teksite\Lareon\Enums\ResponseType;

class ApiFormRequest extends FormRequest
{

    public function failedValidation(Validator $validator)
    {
        return throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => 422,
            'result' => ResponseType::FAILED,
            'data' => [],
        ])->setStatusCode(422));
    }


    public function failedAuthorization()
    {
        return throw new HttpResponseException(response()->json([
            'errors' => ["Forbidden You don't have permission"],
            'status' => 403,
            'result' => ResponseType::FAILED,
            'data' => [],
        ])->setStatusCode(403));
    }
}
