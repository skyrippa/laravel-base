<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class Whatsapp
 * @package App\Utils
 */
class Whatsapp
{
    /**
     * @throws ValidationException
     */
    protected static function auth ()
    {
        $url   = config('appconfig.whatsapp.url');
        $token = config('appconfig.whatsapp.token');

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
     * @param $data
     * @return mixed
     * @throws ValidationException
     */
    public static function send ($data)
    {
        $auth = Whatsapp::auth();

        $validator = Validator::make($data, [
            'phone'   => 'required|numeric',
            'message' => 'required|string|min:1',
        ]);

        if ($validator->fails())
            throw new ValidationException($validator);

        if (config('appconfig.whatsapp.production')) {

            $numbers   = self::whatsappNumbers($data['phone']);
            $response1 = null;
            $response2 = null;

            $message = [
                'message' => $data['message'],
            ];
            if (isset($data['file_link'])) {
                $message['file_link'] = $data['file_link'];
                $message['file_name'] = $data['file_name'];
            }

            if ($numbers[0]) {
                $message['number'] = $numbers[0];

                $response1 = Http::withHeaders(['Authorization' => $auth['token'], 'Content-Type' => 'application/json'])
                    ->post($auth['url'] . "/send-message", $message);

                $response1->successful();
            }

            if ($numbers[1]) {
                $message['number'] = $numbers[1];
                $response2         = Http::withHeaders(['Authorization' => $auth['token'], 'Content-Type' => 'application/json'])
                    ->post($auth['url'] . "/send-message", $message);

                $response2->successful();
            }

            return [json_decode($response1->body()), json_decode($response2->body())];
        }

        return response()->json();
    }

    public static function whatsappNumbers ($phone)
    {
        $number1 = '55';
        $number2 = '55';

        if (strlen($phone) === 11) {
            $number1 .= $phone;
            $number2 .= substr($phone, 0, 2) . substr($phone, 3);
        } elseif (strlen($phone) === 10) {
            $number1 .= $phone;
            $number2 .= substr($phone, 0, 2) . '9' . substr($phone, 2);
        } elseif (strlen($phone) === 13) {
            $number1 = $phone;
            $number2 = substr($phone, 0, 4) . substr($phone, 5);
        } elseif (strlen($phone) === 12) {
            $number1 = $phone;
            $number2 = substr($phone, 0, 4) . '9' . substr($phone, 4);
        }

        return [$number1, $number2];
    }

    public static function sendExample ()
    {
        return Whatsapp::send([
            'phone'   => '5575991822917',
            'message' => 'Primeira mensagem whatsapp'
        ]);
    }
}
