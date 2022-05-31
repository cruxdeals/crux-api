<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    protected $table = "loans";
    protected $guarded = [];


    public function member(){
        return $this->hasOne(Member::class, 'member_id','member_id');
    }
}
