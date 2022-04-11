<?php

namespace App\Services;

use App\Enums\UserRoles;
use App\Models\Client;
use App\Models\User;
use App\Utils\Helpers;
use App\Validators\ClientValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientService
{
    use ServiceTrait;

    public function model()
    {
        return new Client();
    }

    public function validationRules()
    {
        return new ClientValidator();
    }

    protected function relationships()
    {
        return ['user'];
    }

    public function store (Request $request)
    {
        $result = null;

        DB::transaction(function () use ($request, &$result) {

            $this->validationRules()->validate($request->all());

            $password = Helpers::userPasswordGenerator();
            $userData = array_merge($request->all(), ['password' => bcrypt($password)]);
            $clientUser = User::create($userData);
            $clientUser->assignRole(UserRoles::CLIENT);

            $result = $this->model()->create([
                'user_id' => $clientUser->id,
                'name' => $clientUser->name,
            ]);

            $result = array_merge($result->load($this->relationships())->toArray(), [
                'password' => $password,
            ]);
        });

        return $result;
    }

    public function update(Request $request, $id = null)
    {
        $result = null;

        DB::transaction(function () use ($request, &$result, &$id) {

            $result = $this->model()->findOrFail($id);

            $this->validationRules()->validate(array_merge(['id' => $id], $request->all()));

            $result->user()->update([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => Helpers::sanitizeString($request->get('phone')),
                'document' => Helpers::sanitizeString($request->get('document')),
            ]);

            $result->update([
                'name' => $request->get('name'),
            ]);
        });

        return $result->load($this->relationships());
    }
}
