<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiIndicator extends Model
{
    protected $guarded = ['id'];


    public function KpiCategories()
    {
        return $this->belongsTo(KpiCategories::class);
    }
}
