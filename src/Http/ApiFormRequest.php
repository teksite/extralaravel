<?php

namespace Teksite\Extralaravel\Http;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ApiFormRequest extends FormRequest
{

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
            'status'  => Response::HTTP_UNPROCESSABLE_ENTITY,
            'data'    => [],
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }


    public function failedAuthorization(): never
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Forbidden.',
            'errors' => ['auth'=>"Forbidden. You don't have permission"],
            'status' => 403,
            'data' => [],
        ])->setStatusCode(403));
    }
}
