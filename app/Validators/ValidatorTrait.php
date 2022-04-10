<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ValidatorTrait
{
    protected function rules ($data = null)
    {
        if (isset($this->rules)) {
            return $this->rules;
        }

        return [];
    }

    /**
     * @throws ValidationException
     */
    public function validate ($data)
    {
        $validator = Validator::make($data, $this->rules($data));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
