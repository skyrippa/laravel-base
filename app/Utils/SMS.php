<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class SMS
 * @package App\Utils
 */
class SMS
{
    protected static function auth ()
    {
        $url   = config('appconfig.comtele.url');
        $token = config('appconfig.comtele.token');

        $data = [
            'url'   => $url,
            'token' => $token,
        ];

        $validator = Validator::make($data, [
            'url'   => 'required|string|url',
            'token' => 'required|string',
        ]);

        if ($validator->fails())
            throw new ValidationException($validator);

        return $data;
    }

    /**
     * @return integer
     * @throws ValidationException
     */
    public static function credits ()
    {
        $auth = SMS::auth();

        if (config('appconfig.comtele.production')) {
            $response = Http::withHeaders(['auth-key' => $auth['token']])->get($auth['url'] . "/credits");

            $credits = json_decode($response->body());

            return $credits->Object;
        } else {
            return 1;
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws ValidationException
     */
    public static function send ($data)
    {
        $auth = SMS::auth();

        $validator = Validator::make(array_merge(['credits' => SMS::credits()], $data), [
            'phone'   => 'required|numeric',
            'message' => 'required|string|min:1|max:150',
            'credits' => 'required|numeric|min:1',
        ]);

        if ($validator->fails())
            throw new ValidationException($validator);

        if (config('appconfig.comtele.production')) {

            $response = Http::withHeaders(['auth-key' => $auth['token']])->post($auth['url'] . "/send", [
                'Receivers' => $data['phone'],
                'Content'   => $data['message'],
            ]);

            $response->successful();

            return json_decode($response->body());
        }

        return response()->json();
    }

    public static function sendExample ()
    {
        return SMS::send([
            'phone'   => '75991822917',
            'message' => 'Primeira mensagem sms'
        ]);
    }
}
