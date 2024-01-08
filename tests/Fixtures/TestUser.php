<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $fillable = [
        'id',
        'code',
    ];
}
