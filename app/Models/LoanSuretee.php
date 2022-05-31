<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSuretee extends Model
{
    use HasFactory;
    protected $table = "loan_surety";
    protected $guarded = [];
}
