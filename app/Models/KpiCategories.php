<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiCategories extends Model
{

    protected $guarded = ['id'];

    public function KpiIndicator()
    {
        return $this->hasMany(KpiIndicator::class);
    }
}
