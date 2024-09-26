<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaContent extends Model
{
    use HasFactory;

    protected $appends = [
        'logo'
    ];

    protected $fillable = [
        'page_name',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    public function getLogoAttribute(){
        $globalInfo = GlobalSettings::first();
        return asset($globalInfo->logo);
    }
}
