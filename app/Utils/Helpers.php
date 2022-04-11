<?php

namespace App\Utils;

use App\Enums\Enums;
use App\Http\Resources\DefaultResource;

class Helpers
{
    public static function paginateCollection ($collection, $perPage = 20, $pageName = 'page', $fragment = null)
    {
        $currentPage      = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName);
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage);
        parse_str(request()->getQueryString(), $query);
        unset($query[$pageName]);
        return DefaultResource::collection(new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'pageName' => $pageName,
                'path'     => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'query'    => $query,
                'fragment' => $fragment
            ]
        ));
    }

    public static function legalEntity ($document)
    {
        $cpf_cnpj = preg_replace('/[^a-zA-Z0-9_ -]/s', ' ', $document);
        return strlen($cpf_cnpj) == 14 ? 'PJ' : 'PF';
    }

    public static function centsToMoney ($cents, $pattern = 'BRL')
    {
        return number_format(($cents / 100), 2, $pattern == 'BRL' ? ',' : '.', '');
    }

    public static function userPasswordGenerator ($length = 6)
    {
        return strtolower(substr(md5(uniqid()), 0, $length));
    }

    public static function sanitizeString ($str)
    {
        return preg_replace('/[^0-9]/', '', $str);
    }

    public static function sanitizeStringWithLetters ($str)
    {
        return preg_replace("~[^A-Za-z0-9]~", '', $str);
    }

    public static function maskDocument ($document)
    {
        if (strlen($document) === 11) {
            return self::mask(Enums::MASKS['cpf'], $document);
        } elseif (strlen($document) === 14) {
            return self::mask(Enums::MASKS['cnpj'], $document);
        }
    }

    public static function mask ($mask, $str)
    {
        $str = str_replace(" ", "", $str);

        for ($i = 0; $i < strlen($str); $i++) {
            $mask[strpos($mask, "#")] = $str[$i];
        }

        return $mask;
    }

    public static function numberToText ($value, $locale = 'pt-br')
    {
        $f = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);

        return $f->format($value);
    }

    public static function centsToText ($value)
    {
        $valString = strval($value);

        $reais = substr($valString, 0, strlen($valString) - 2);

        $cents = substr($valString, -2);

        $text = self::numberToText((int)$reais);

        if ((int)$reais === 1) {
            $text .= ' real';
        } else {
            $text .= ' reais';
        }

        if ((int)$cents > 0) {

            $text .= ' e ' . self::numberToText((int)$cents);

            if ((int)$cents === 1) {
                $text .= ' centavo';
            } else {
                $text .= ' centavos';
            }
        }

        return ucfirst($text);
    }

    public static function genRandomString ($length = 10, $steps = 3)
    {
        $characters = '';
        $numbers    = '0123456789';
        $lowercase  = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($steps == 1) {
            $characters .= $numbers;
        } elseif ($steps == 2) {
            $characters .= $numbers . $lowercase;
        } elseif ($steps == 3) {
            $characters .= $numbers . $lowercase . $uppercase;
        }

        $charactersLength = strlen($characters);
        $randomString     = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
