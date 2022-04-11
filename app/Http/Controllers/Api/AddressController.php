<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerTrait;
use App\Services\AddressService;

class AddressController extends Controller
{
    use ControllerTrait;

    public function __construct(AddressService $service)
    {
        $this->service = $service;

        $this->middleware('permission:addresses:list')->only(['index', 'indexAll']);
        $this->middleware('permission:addresses:create')->only('store');
        $this->middleware('permission:addresses:edit')->only('update');
        $this->middleware('permission:addresses:show')->only('show');
        $this->middleware('permission:addresses:delete')->only(['destroy', 'restore']);
        $this->middleware('permission:addresses:audits')->only('audits');
    }
}
