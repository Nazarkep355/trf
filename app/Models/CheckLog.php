<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckLog extends Model
{

    protected $table = 'traffic_app_securitychecklog';
//    protected $guarded = false;
    public $timestamps = false;


    public function user_by_ip() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BadUser::class, 'ip_address', 'address');
    }

    public function last_log() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
       return $this->hasOne(LogLine::class, 'address', 'address')->orderBy('id', 'desc');
    }
}
