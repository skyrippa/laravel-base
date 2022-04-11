<?php

namespace App\Validators;

use Illuminate\Validation\Rule;

class ClientValidator
{
    use ValidatorTrait;

    protected function rules($data = null)
    {
        $user_id = $data['id'] ?? null;

        return [
            'name' => 'required|string',
            'document'            => [
                'required',
                'cpf_cnpj',
                Rule::unique('users', 'document')->ignore($user_id),
            ],
            'email'               => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user_id),
            ],
            'phone'               => [
                'required',
                Rule::unique('users', 'phone')->ignore($user_id),
            ],
        ];
    }
}
