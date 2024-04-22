<?php

namespace Ngocnm\LaravelHelpers\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ngocnm\LaravelHelpers\casts\PathCast;
use Ngocnm\LaravelHelpers\eloquent\BaseModel;

class BackupFile extends Model
{
    use HasFactory, BaseModel;

    protected $table = 'backup_files';

    protected $casts = [
        'path' => PathCast::class,
    ];

    protected $fillable = [
        'path',
        'status',
        'created_at',
        'updated_at'
    ];

    static $schema = [
        "id" => [
            "type" => "int",
            "insert" => false,
            "query_condition" => true,
            "sort" => true
        ],
        "path" => [
            "type" => "string",
            "insert" => false,
            "query_condition" => false,
            "required_when_create" => false,
            "sort" => true
        ],
        "status" => [
            "type" => "integer",
            "insert" => false,
            "query_condition" => false,
            "required_when_create" => false,
            "sort" => true
        ],
        "updated_at" => [
            "type" => "string",
            "insert" => false,
            "query_condition" => false,
            "required_when_create" => false,
            "sort" => true
        ],
        "created_at" => [
            "type" => "string",
            "insert" => false,
            "query_condition" => false,
            "required_when_create" => false,
            "sort" => true
        ]
    ];
}