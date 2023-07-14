<?php

namespace Ngocnm\LaravelHelpers\eloquent;
use Ngocnm\LaravelHelpers\Helper;

trait BaseModel
{
    static function baseQueryBuilder($model)
    {
        $model = $model::select(self::alias(Helper::BaseApiRequest()->getFields()));
        if (Helper::BaseApiRequest()->getWhere()) $model = self::whereQueryBuilder(Helper::BaseApiRequest()->getWhere(), $model);
        if (Helper::BaseApiRequest()->getWith()) $model = self::withQueryBuilder(Helper::BaseApiRequest()->getWith(), $model);
        if (Helper::BaseApiRequest()->getKeywordSearch()) $model = self::fullTextSearch($model,Helper::BaseApiRequest()->getKeywordSearch(),Helper::BaseApiRequest()->getFieldSearch());
        return $model;
    }

    static function alias($fields = null)
    {
        $columns = array_keys(self::$schema);
        if ($fields == "*" || empty($fields)) {
            $newFields = $columns;
        } else {
            $fields = explode(',', $fields);
            $newFields = array_filter($columns, function ($item) use ($fields) {
                return in_array($item, $fields);
            });
        }
        if (empty($newFields)) $newFields = "*";
        return $newFields;
    }

    static function whereQueryBuilder($where, $model)
    {
        if (empty(self::$schema)) {
            throw new \Exception(self::class . ': Model class not define $field_schema');
        }
        $options = explode(',', $where);
        foreach ($options as $value) {
            $value = explode(" ", $value);
            if (isset($value[1]) && key_exists($value[0], self::$schema) && (isset(self::$schema[$value[0]]['query_condition']) && self::$schema[$value[0]]['query_condition'] == true)) {
                $data_column = self::$schema[$value[0]];
                $type = $data_column['type'];
                $column_name = $value[0];
                switch ($type) {
                    case 'int':
                        $value[1] = (int)$value[1];
                        $model = $model->where($column_name, $value[1]);
                        break;
                    case 'double':
                        $value[1] = (double)$value[1];
                        $model = $model->where($column_name, $value[1]);
                        break;
                    case 'string':
                        $value[1] = trim($value[1]);
                        if (in_array($value[1], $data_column['values'])) {
                            $model = $model->where($column_name, $value[1]);
                        }
                        break;
                }
            }
        }
        return $model;
    }

    static function withQueryBuilder($with, $model)
    {
        $with = explode("-", $with);
        $with_query = [];
        foreach ($with as $with_item) {
            $with_item = explode(" ", $with_item);
            $relationship = $with_item[0];
            $fields = isset($with_item[1]) ? explode(",", $with_item[1]) : null;
            if (!method_exists(self::class, $relationship) || !defined('self::relationship_' . $relationship . '_fields')) continue;
            if (!empty($fields)) {
                $fields = array_filter(constant('self::relationship_' . $relationship . '_fields'), function ($item) use ($fields) {
                    return in_array($item, $fields);
                });
                if (count($fields) != 0) {
                    $relationship .= ":" . implode(",", $fields);
                }
                $with_query[] = $relationship;
            }
        }
        if (count($with_query) != 0) $model->with($with_query);
        return $model;
    }

    static function fullTextSearch($model, $keyword, $field)
    {
        if (
            key_exists($field, self::$schema)
            && !empty($keyword)
            && isset(self::$schema[$field]['fulltext_search'])
            && self::$schema[$field]['fulltext_search']==true
        ) {
            return $model->whereRaw("MATCH($field) AGAINST('$keyword')");
        }
        return $model;
    }

    static function getFillableCreate()
    {
        $fillable = [];
        foreach (self::$schema as $field => $data) {
            if (isset($data['insert']) && $data['insert'] === true) $fillable[] = $field;
        }
        return $fillable;
    }

}