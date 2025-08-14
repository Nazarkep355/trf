<?php

namespace App\Models;

use App\Services\BanService;
use Illuminate\Database\Eloquent\Model;

class BadUser extends Model
{
    protected $primaryKey = 'ip_address';
    protected $table = 'traffic_app_baduser';
    protected $casts = [
        'ip_address' => 'string',
    ];
//    protected $guarded = false;
    public $timestamps = false;
    public function setStatusAttribute($value)
    {
//        \Log::info($this->ip_address);
//        dd($this->ip_address);
        if(in_array($value, ['banned', 'unbanned'])) {
            $banService = new BanService();
            if ($value == 'banned') {
                $banService->banIP($this->ip_address);
            } else {
                $banService->unBanIp($this->ip_address);
            }

        } else {
            $this->attributes['status'] = $value;
            $this->save();
        }


    }


}
