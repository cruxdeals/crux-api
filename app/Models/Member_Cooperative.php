<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member_Cooperative extends Model
{
    use HasFactory;
    public $table = "members_cooperatives";
    public $guarded = [];
}
