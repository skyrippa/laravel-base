<?php

namespace App\Http\Controllers;

use App\Services\ServiceTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Client\ConnectionException;

trait ControllerTrait
{
    protected $service;

    public function __construct (ServiceTrait $service)
    {
        $this->service = $service;
    }

    public function index (Request $request)
    {
        try {
            return $this->service->setModelType($request->model_type)->setId($request->model_id)->index($request);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function indexAll (Request $request)
    {
        try {
            return $this->service->setModelType($request->model_type)->setId($request->model_id)->indexAll($request);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function store (Request $request)
    {
        try {
            $data = $this->service->setModelType($request->model_type)->setId($request->model_id)->store($request);
            return response()->json($data, 201);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (ConnectionException $c) {
            return $this->error('Falha ao se comunicar com servidor externo');
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function show (Request $request, $id = null)
    {
        try {
            return response()->json($this->service->setModelType($request->model_type)->setId($request->model_id)->show($request, $id));
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function update (Request $request, $id = null)
    {
        try {
            $data = $this->service->setModelType($request->model_type)->setId($request->model_id)->update($request, $id);
            return response()->json($data);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function destroy (Request $request, $id = null)
    {
        try {
            $this->service->setModelType($request->model_type)->setId($request->model_id)->destroy($request, $id);
            return response()->json(null, 204);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function restore (Request $request, $id = null)
    {
        try {
            $data = $this->service->setModelType($request->model_type)->setId($request->model_id)->restore($request, $id);
            return response()->json($data);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function audits (Request $request, $id = null)
    {
        try {
            $result = $this->service->setModelType($request->model_type)->setId($request->model_id)->audits($request, $id);
            return response()->json($result);
        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }

    public function createComment (Request $request, $id)
    {
        try {
            $result = $this->service->setModelType($request->model_type)->setId($request->model_id)->createComment($request, $id);
            return response()->json($result);
        } catch (ModelNotFoundException $m) {
            return $this->error("Not Found!", 404);
        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);
        } catch (QueryException $q) {
            return $this->error($q->getMessage(), 500);
        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());
            return $this->error($e->getMessage());
        }
    }
}
