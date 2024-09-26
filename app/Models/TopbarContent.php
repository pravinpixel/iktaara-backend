<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopbarContent extends Model
{
    use HasFactory;

    protected $fillable = ['content','enabled'];


}
