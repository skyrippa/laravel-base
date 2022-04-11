<?php

namespace App\Services;

use App\Models\Address;
use App\Validators\AddressValidator;
use Illuminate\Support\Facades\Auth;

class AddressService
{
    use ServiceTrait;

    public function model()
    {
        if (Auth::user()->isSuperAdmin()) {
            return new Address();
        } else {
            return $this->modelType::find($this->parentId)->addresses();
        }
    }

    public function validationRules()
    {
        return new AddressValidator();
    }

    protected function relationships()
    {
        return [
           'client', 'city', 'state',
        ];
    }
}
