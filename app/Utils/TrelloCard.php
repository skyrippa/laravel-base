<?php

namespace App\Utils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TrelloCard
{
    const ID_LIST_SUPORTE_CLIENTES = '';

    /**
     * @param $list_id
     * @param $data
     * @return mixed
     * @throws ValidationException
     */
    public static function new ($list_id, $data)
    {
        $return = null;

        $validator = Validator::make(array_merge(['list_id' => $list_id], $data), [
            'list_id' => 'required|string',
            'name'    => 'required|string',
            'desc'    => 'required|string',
        ]);

        if ($validator->fails())
            throw new ValidationException($validator);

        $url = 'https://api.trello.com/1/cards';


        $key_trello   = config('appconfig.trello.key');
        $token_trello = config('appconfig.trello.token');

        $url_params = "?key={$key_trello}&token={$token_trello}&idList={$list_id}";

        $url = $url . $url_params;

        if (config('appconfig.trello.production')) {

            $idLabels = data_get($data, 'label_ids');

            if($idLabels) $idLabels = [$idLabels];

            if(App::environment('testing') && config('appconfig.trello.label_test')){
                $idLabels[] = config('appconfig.trello.label_test');
            }

            $response = Http::post($url, array_merge($data, [
                    'idLabels' => $idLabels,
                ])
            );

            $response->successful();

            $return = $response->body();
        }

        return App::environment('testing') ? $return : json_decode($return);
    }

    public static function sendExample ()
    {
        return TrelloCard::new(TrelloCard::ID_LIST_SUPORTE_CLIENTES, [
            'name' => '[Pré-cadastro] - Empresa Fantasia Atakadão',
            'desc' => "**Empresa Fantasia Atakadão**
                           \n- **ID**: 21825-155ds-sd45sd-584
                           \n- **Nome do cliente**: Super mercado Mais barato só amanhã"
        ]);
    }
}
