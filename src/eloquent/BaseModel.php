<?php

namespace Ngocnm\LaravelHelpers\eloquent;

use Ngocnm\LaravelHelpers\Helper;

trait BaseModel
{
    static function baseQueryBuilder($model)
    {
        $model = $model::select(self::alias((new $model)->table,Helper::BaseApiRequest()->getFields()));
        if (Helper::BaseApiRequest()->getWhereNot()) $model = self::whereNotQueryBuilder(Helper::BaseApiRequest()->getWhereNot(), $model);
        if (Helper::BaseApiRequest()->getWhere()) $model = self::whereQueryBuilder(Helper::BaseApiRequest()->getWhere(), $model);
        if (Helper::BaseApiRequest()->getWhereIn()) $model = self::whereInQueryBuilder(Helper::BaseApiRequest()->getWhereIn(), $model);
        if (Helper::BaseApiRequest()->getWith()) $model = self::withQueryBuilder(Helper::BaseApiRequest()->getWith(), $model);
        if (Helper::BaseApiRequest()->getKeywordSearch()) $model = self::fullTextSearch($model, Helper::BaseApiRequest()->getKeywordSearch(), Helper::BaseApiRequest()->getFieldSearch());
        if (Helper::BaseApiRequest()->getOrderBy()) $model = self::orderByQueryBuilder(Helper::BaseApiRequest()->getOrderBy(), $model);
        return $model;
    }

    static function alias($table,$fields = null)
    {
        $columns = array_keys(self::$schema);
        if ($fields == "*" || empty($fields) || !is_string($fields)) {
            $newFields = $columns;
        } else {
            $fields = explode(',', $fields);
            $newFields = array_filter($columns, function ($item) use ($fields) {
                return in_array($item, $fields);
            });
            if(empty($newFields)) $newFields = $columns;
        }
        $newFields = array_map(function ($item) use($table){
            return "$table.$item";
        },$newFields);
        return $newFields;
    }

    static function whereQueryBuilder($where, $model)
    {
        if (empty(self::$schema)) {
            throw new \Exception(self::class . ': Model class not define $field_schema');
        }
        $options = explode(',', $where);
        $table = (new self())->table;
        foreach ($options as $value) {
            $value = explode(" ", $value);
            if (isset($value[1]) && key_exists($value[0], self::$schema) && (isset(self::$schema[$value[0]]['query_condition']) && self::$schema[$value[0]]['query_condition'] == true)) {
                $data_column = self::$schema[$value[0]];
                $type = $data_column['type'];
                $column_name = $table.".".$value[0];
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

    static function whereInQueryBuilder($where, $model){
        if (empty(self::$schema)) {
            throw new \Exception(self::class . ': Model class not define $field_schema');
        }
        $where_list = is_string($where)?[$where]:$where;
        $table = (new self())->table;
        foreach ($where_list as $where_in){
            $value = explode(" ", $where_in);
            if (isset($value[1]) && key_exists($value[0], self::$schema) && (isset(self::$schema[$value[0]]['query_condition']) && self::$schema[$value[0]]['query_condition'] == true)){
                $column = "{$table}.{$value[0]}";
                $data_column = self::$schema[$value[0]];
                $type = $data_column['type'];
                $values = explode(",",$value[1]);
                $values = array_map(function ($item) use($type){
                    switch ($type){
                        case 'int':
                            $item = (int) $item;
                            break;
                        case 'double':
                            $item = (double) $item;
                            break;
                        case 'string':
                            $item = trim($item);
                            break;
                    }
                    return (int) $item;
                },$values);
                $values = array_unique($values);
                if(count($values)!=0){
                    $model = $model->whereIn($column, $values);
                }
            }
        }
        return $model;
    }

    static function whereNotQueryBuilder($where, $model)
    {
        if (empty(self::$schema)) {
            throw new \Exception(self::class . ': Model class not define $field_schema');
        }
        $options = explode(',', $where);
        $table = (new self())->table;
        foreach ($options as $value) {
            $value = explode(" ", $value);
            if (isset($value[1]) && key_exists($value[0], self::$schema) && (isset(self::$schema[$value[0]]['query_condition']) && self::$schema[$value[0]]['query_condition'] == true)) {
                $data_column = self::$schema[$value[0]];
                $type = $data_column['type'];
                $column_name = $value[0];
                switch ($type) {
                    case 'int':
                        $value[1] = (int)$value[1];
                        $model = $model->where($table.".".$column_name, '!=', $value[1]);
                        break;
                    case 'double':
                        $value[1] = (double)$value[1];
                        $model = $model->where($table.".".$column_name, '!=', $value[1]);
                        break;
                    case 'string':
                        $value[1] = trim($value[1]);
                        if (in_array($value[1], $data_column['values'])) {
                            $model = $model->where($table.".".$column_name, '!=', $value[1]);
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
        $table = (new self())->table;
        if (
            key_exists($field, self::$schema)
            && !empty($keyword)
            && isset(self::$schema[$field]['fulltext_search'])
            && self::$schema[$field]['fulltext_search'] == true
        ) {
            return $model->whereRaw("MATCH($table.$field) AGAINST('$keyword' IN BOOLEAN MODE)");
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

    static function orderByQueryBuilder($order_by, $model)
    {
        $table = (new self())->table;
        if (empty(self::$schema)) {
            throw new \Exception(self::class . ': Model class not define $field_schema');
        }
        $options = explode(',', $order_by);
        foreach ($options as $value) {
            $value =  explode(" ", $value);
            if(count($value)!=2) continue;
            list($field, $type_order) = $value;
            if (empty($type_order) || !in_array($type_order, ['asc', 'desc'])) continue;
            if (!key_exists($field, self::$schema)
                || !isset(self::$schema[$field]['sort'])
                || self::$schema[$field]['sort'] != true) {
                continue;
            }

            $model->orderBy($table.".".$field, $type_order);

        }
        return $model;
    }

}
