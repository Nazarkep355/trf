<?php

namespace App\Providers;

use App\Models\BadUserAgent;
use SleepingOwl\Admin\Admin;
use SleepingOwl\Admin\Providers\AdminSectionsServiceProvider as ServiceProvider;
use URL;

class AdminSectionsServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $sections = [
        //\App\User::class => 'App\Http\Sections\Users',
        \App\Models\LogLine::class => 'App\Http\Sections\LogsPage',
        \App\Models\CheckLog::class  => 'App\Http\Sections\CheckLogPage',
        \App\Models\BadUserAgent::class => 'App\Http\Sections\BadUserAgentPage',
    ];

    /**
     * Register sections.
     *
     * @param Admin $admin
     * @return void
     */
    public function boot(Admin $admin)
    {
    	//
        \DB::enableQueryLog();
        URL::forceScheme('https');
        parent::boot($admin);
    }
}
