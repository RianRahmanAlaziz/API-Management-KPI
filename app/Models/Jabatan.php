<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jabatan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
}
