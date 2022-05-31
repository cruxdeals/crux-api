<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    use HasFactory;
    protected $table = "cooperatives";
    protected $guarded = [];

    public function members()
    {
        return $this->hasMany(Member::class, 'coop_id','coop_id');
    }
}
