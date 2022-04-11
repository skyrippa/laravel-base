<?php

namespace App\Validators;

class AddressValidator
{
    use ValidatorTrait;

    protected function rules($data = null)
    {
        return [
            'zip_code'     => 'required',
            'street'       => 'required|max:255',
            'house_number' => 'required|numeric',
            'neighborhood' => 'required|max:255',
            'state_id'     => 'required|numeric|exists:states,id',
            'city_id'      => 'required|numeric|exists:cities,id',
        ];
    }
}
