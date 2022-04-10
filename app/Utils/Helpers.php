<?php

namespace App\Utils;

use App\Enums\Enums;
use App\Http\Resources\DefaultResource;
use Illuminate\Http\Request;

class Helpers
{
    public static function indexQueryBuilder (Request $request, array $relationships, $model, $order_by = 'asc', $fields = ['*'])
    {
        $limit = $request->all()['limit'] ?? 20;

        if ($request->paginate === "false")
            $request->merge(['paginate' => false]);

        $order = $request->all()['order'] ?? null;
        if ($order !== null) {
            $order = explode(',', $order);
        }
        $order[0] = $order[0] ?? 'id';
        $order[1] = $order[1] ?? $order_by;

        $wheres   = $request->all()['where'] ?? [];
        $betweens = $request->all()['between'] ?? [];

        $likes   = $request->all()['like'] ?? null;
        $searchs = $request->all()['search'] ?? null;

        $result = $model->orderBy($order[0], $order[1])
            ->where(function ($query) use ($wheres, $likes, $betweens) {
                if (gettype($likes) == 'array') {
                    foreach ($likes as $like) {
                        $like = explode(',', $like);
                        if (count($like) != 2) throw new \Exception('Invalid "like" parameters, expected 2 passes ' . count($like));

                        $like[1] = '%' . $like[1] . '%';

                        if (strpos($like[0], '.') !== false) {
                            $relation = explode('.', $like[0]);
                            $query->whereHas($relation[0], function ($query) use ($relation, $like) {
                                $query->where($relation[1], 'like', $like[1]);
                            });
                        } else {
                            $query->where($like[0], 'like', $like[1]);
                        }
                    }
                }


                if (gettype($wheres) == 'array') {
                    foreach ($wheres as $where) {
                        $where = explode(',', $where);
                        if (count($where) < 2) throw new \Exception('Invalid "where" parameters, expected 3 passes ' . count($where));

                        if ($where[1] == 'in') {
                            if (strpos($where[0], '.') !== false) {
                                $relations = explode('.', $where[0]);

                                $query->whereHas($relations[0], function ($query) use ($relations, $where) {
                                    $values = array_slice($where, 2);

                                    if (count($relations) > 2) {
                                        $query->whereHas($relations[1], function ($query) use ($relations, $values) {
                                            $query->whereIn($relations[2], $values);
                                        });
                                    } else {
                                        $query->whereIn($relations[1], $values);
                                    }
                                });
                            } else {
                                $query->whereIn($where[0], array_slice($where, 2));
                            }
                        } elseif (strpos($where[0], '.') !== false) {
                            $relations = explode('.', $where[0]);

                            $query->whereHas($relations[0], function ($query) use ($relations, $where) {
                                $values = array_slice($where, 1);

                                if (count($relations) > 2) {
                                    $query->whereHas($relations[1], function ($query) use ($relations, $values) {
                                        foreach ($values as $value) {
                                            $query->where($relations[2], $value);
                                        }
                                    });
                                } else {
                                    foreach ($values as $value) {
                                        $query->where($relations[1], $value);
                                    }
                                }
                            });
                        } elseif (count($where) === 3) {
                            $query->where($where[0], $where[1], $where[2]);
                        } else {
                            $query->where($where[0], $where[1]);
                        }
                    }
                }

                if (gettype($betweens) == 'array') {

                    foreach ($betweens as $between) {
                        $between = explode(',', $between);
                        if (count($between) != 3) throw new \Exception('Invalid "between" parameters, expected 3 passes ' . count($between));

                        if (strpos($between[0], '.') !== false) {
                            $relations = explode('.', $between[0]);

                            $query->whereHas($relations[0], function ($query) use ($relations, $between) {
                                $dates = array_slice($between, 1);

                                if (count($relations) > 2) {
                                    $query->whereHas($relations[1], function ($query) use ($relations, $dates) {
                                        $query->whereBetween($relations[2], ["{$dates[0]} 00:00:00", "{$dates[1]} 23:59:59"]);
                                    });
                                } else {
                                    $query->whereBetween($relations[1], ["$dates[0] 00:00:00", "$dates[1] 23:59:59"]);
                                }
                            });
                        } else {
                            $query->whereBetween($between[0], ["$between[1] 00:00:00", "$between[2] 23:59:59"]);
                        }
                    }
                }
//                dd($query->toSql(), $query->getBindings());
                return $query;
            })
            ->where(function ($query) use ($searchs) {
                if (gettype($searchs) == 'array') {
                    foreach ($searchs as $item) {
                        $item = explode(',', $item);
                        if (count($item) != 2) throw new \Exception('Invalid "search" parameters, expected 2 passes ' . count($item));
                        $item[1] = '%' . $item[1] . '%';

                        if (strpos($item[0], '.') !== false) {
                            $relation = explode('.', $item[0]);

                            $query->whereHas($relation[0], function ($query) use ($relation, $item) {
                                $query->orWhere($relation[1], 'like', $item[1]);
                            });
                        } else {
                            $query->orWhere($item[0], 'like', $item[1]);
                        }
                    }
                }
//                dd($query->toSql(), $query->getBindings());
            })
            ->with($relationships);

        if ($request->get('paginate', true)) {
            $result = $result->paginate($limit, $fields);
        } else {
            $result = $result->get($fields);
        }

        return $result;
    }

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

    public static function cardNumberGenerator ()
    {
        $number = array();

        array_push(
            $number,
            9999,
            rand(1000, 9999),
            rand(1000, 9999),
            rand(1000, 9999),
        );

        return implode(" ", $number);
    }

    public static function cardHashGenerator ()
    {
        return (string)rand(1000000000000000, 9999999999999999);
    }

    public static function centsToMoney ($cents, $pattern = 'BRL')
    {
        return number_format(($cents / 100), 2, $pattern == 'BRL' ? ',' : '.', '');
    }

    public static function cardPasswordGenerator ()
    {
        return rand(1000, 9999);
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
