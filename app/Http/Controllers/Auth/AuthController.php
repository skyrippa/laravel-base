<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $service;

    public function __construct (AuthService $service)
    {
        $this->service = $service;
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse [string] access_token
     */
    public function login (Request $request)
    {
        try {
            return $this->service->login($request);

        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);

        } catch (Exception $e) {

            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return string [string] message
     */
    public function logout (Request $request)
    {
        try {
            return $this->service->logout($request);

        } catch (Exception $e) {

            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }

    /**
     * Get current user
     *
     * @param Request $request
     * @return string [string] message
     */
    public function user (Request $request)
    {
        try {
            return $this->service->user($request);

        } catch (Exception $e) {

            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }

    public function passwordRecovery (Request $request)
    {
        try {
            return $this->service->passwordRecovery($request);

        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);

        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);

        } catch (Exception $e) {

            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }

    public function resetPassword (Request $request)
    {
        try {
            return $this->service->resetPassword($request);

        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);

        } catch (Exception $e) {

            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }
}
