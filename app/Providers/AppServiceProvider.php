<?php

namespace App\Providers;

use App\Models\IjinKeluar;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('partials.header', function ($view) {
            $view->with(
                'jumlahNotifikasi',
                IjinKeluar::whereNull('notification_read_at')->count()
            );
        });
    }
}
