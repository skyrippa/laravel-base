<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerTrait;
use App\Services\ClientService;

class ClientController extends Controller
{
    use ControllerTrait;

    public function __construct(ClientService $service)
    {
        $this->service = $service;

        $this->middleware('permission:clients:list')->only(['index', 'indexAll']);
        $this->middleware('permission:clients:create')->only('store');
        $this->middleware('permission:clients:edit')->only('update');
        $this->middleware('permission:clients:show')->only('show');
        $this->middleware('permission:clients:delete')->only(['destroy', 'restore']);
        $this->middleware('permission:clients:audits')->only('audits');
    }
}
