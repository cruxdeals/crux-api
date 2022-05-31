<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Member extends User implements JWTSubject
{
    use HasFactory;
    protected $table = "members";
    protected $guarded = [];

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
}
