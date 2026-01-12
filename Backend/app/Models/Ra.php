<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ra extends Model
{
    protected $table="ra";
    protected $primary="ID";
    protected $incrementing=false;
    protected $fillable=[
        "Descripcion"
    ];

    public function compRa(){
        return $this->hasOne(compRa::class,"comp_ra","ID","ID_Ra");
    }

}
