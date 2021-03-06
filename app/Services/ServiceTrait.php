<?php

namespace App\Services;

use App\Http\Resources\DefaultCollection;
use App\Utils\Helpers;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AuditCollection;

trait ServiceTrait
{
    protected $parentId;
    protected $modelType;

    abstract function model ();

    public function validationRules ()
    {
    }

    public function resourceCollection ()
    {
        return DefaultCollection::class;
    }

    protected function relationships ()
    {
        if (isset($this->relationships)) {
            return $this->relationships;
        }

        return [];
    }

    public function apiResource ()
    {
    }

    public function setId ($id)
    {
        $this->parentId = $id;
        return $this;
    }

    public function setModelType ($type)
    {
        $this->modelType = $type;
        return $this;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function index (Request $request)
    {
        $result = $this->model()->with($this->relationships())->get();
        $resourceCollection = $this->resourceCollection();
        return Helpers::paginateCollection(new $resourceCollection($result));
    }

    /**
     * Display a listing of the resource, including soft deleted ones.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function indexAll (Request $request)
    {
        $result = $this->model()->withTrashed()->with($this->relationships())->get();
        $resourceCollection = $this->resourceCollection();
        return Helpers::paginateCollection(new $resourceCollection($result));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store (Request $request)
    {
        $result = null;

        DB::transaction(function () use ($request, &$result) {

            $this->validationRules()->validate($request->all());

            $result = $this->model()->create($request->all());
            $result->refresh();
        });

        return $result->load($this->relationships());
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function show (Request $request, $id = null)
    {
        return $this->model()->with($this->relationships())
            ->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update (Request $request, $id = null)
    {
        $result = null;

        DB::transaction(function () use ($request, &$result, &$id) {

            $result = $this->model()->findOrFail($id);

            $this->validationRules()->validate(array_merge(['id' => $id], $request->all()));

            $result->update($request->all());
            $result->refresh();
        });

        return $result->load($this->relationships());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy (Request $request, $id = null)
    {
        $result = null;

        DB::transaction(function () use (&$request, &$result, &$id) {

            $result = $this->model()->findOrFail($id);
            $result->delete();
        });

        return $result;
    }

    /**
     * Restore the specified resource to the storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function restore (Request $request, $id = null)
    {
        $result = null;

        DB::transaction(function () use (&$request, &$result, &$id) {
            $result = $this->model()->withTrashed()->findOrFail($id);
            $result->restore();
        });

        return $result->load($this->relationships());
    }

    public function audits (Request $request, $id = null)
    {
        $data = $this->model()->withTrashed()->findOrFail($id);

        $audits = $data->audits()->with('user')->get();

        return new AuditCollection($audits);
    }
}
