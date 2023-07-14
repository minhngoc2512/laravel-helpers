<?php

namespace Ngocnm\LaravelHelpers;


use Illuminate\Support\Facades\Request;

class RequestHelper
{
    use SingletonTrait;

    private $page = 1;

    private $fields = '*';

    private $where = null;
    private $where_in = null;
    private $where_range = null;
    private $limit = 30;
    private $offset = 0;
    private $order_by = null;
    private $with = null;

    private $field_search = null;

    private $keyword = null;

    public function filterRequest()
    {
        if (Request::has('page')) {
            $page = (int)Request::input('page');
            if ($page < 1 || $page > 100) {
                $this->page = 1;
            } else {
                $this->page = $page;
            }
        }

        if (Request::has('fields')) $this->fields = Request::input('fields');
        if (Request::has('where')) $this->where = urldecode(Request::input('where'));
        if (Request::has('where_in')) $this->where_in = urldecode(Request::input('where_in'));
        if (Request::has('where_range')) $this->where_range = urldecode(Request::input('where_range'));
        if (Request::has('order_by')) $this->order_by = Request::input('order_by');
        if (Request::has('with')) $this->with = trim(urldecode(Request::input('with')));
        if (Request::has('limit')) {
            $limit = (int)Request::input('limit');
            if ($limit < 1 || $limit > 100) {
                $this->limit = 30;
            } else {
                $this->limit = (int)Request::input('limit');
            }
        }
        if (Request::has('offset')) {
            $this->offset = (int)Request::input('offset');
        } else {
            $this->offset = $this->limit * $this->page - $this->limit;
        }
        if(Request::has('field_search')&&Request::has('keyword')&&!empty(Request::input('keyword'))){
            $keyword = StringHelper::filter(Request::input('keyword'));
            if(!empty($keyword)){
                $this->keyword = $keyword;
                $this->field_search = Request::input('field_search');
            }
        }
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getWhere()
    {
        return $this->where;
    }
    public function getWhereIn()
    {
        return $this->where_in;
    }
    public function getWhereRange()
    {
        return $this->where_range;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getOrderBy()
    {
        return $this->order_by;
    }

    public function getWith()
    {
        return $this->with;
    }

    public function getFieldSearch(){
        return $this->field_search;
    }

    public function getKeywordSearch(){
        return $this->keyword;
    }
}
