<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
        $this->matAppRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace . '\PC')
             ->group(base_path('routes/pc.php'));

        Route::middleware('web')
            ->prefix('m')
            ->namespace($this->namespace . '\Mobile')
            ->group(base_path('routes/mobile.php'));

        Route::middleware('web')
            ->prefix('static')
            ->namespace($this->namespace . '\StaticHtml')
            ->group(base_path('routes/static.php'));

        Route::middleware('web')
            ->prefix('admin')
            ->namespace($this->namespace . '\Admin')
            ->group(base_path('routes/admin.php'));
    }

    protected function matAppRoutes(){
        Route::middleware('api')
            ->prefix('m')
            ->namespace($this->namespace . '\App')
            ->group(base_path('routes/app.php'));

        Route::middleware('api')
            ->prefix('m/v100')
            ->namespace($this->namespace . '\App')
            ->group(base_path('routes/app/app_v100.php'));

        //刷新图片验证码
        Route::middleware('web')
            ->get("/app/user/refresh_verify_code", $this->namespace . '\App'."\\AuthController@refreshVerifyCode");
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
