<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMiddleware();
        $this->configureAuthEvents();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Register middleware aliases.
     */
    protected function configureMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('admin', EnsureUserIsAdmin::class);
        $router->aliasMiddleware('active', EnsureUserIsActive::class);
    }

    /**
     * Log auth events for activity tracking.
     */
    protected function configureAuthEvents(): void
    {
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if ($event->user instanceof User) {
                ActivityLogger::log('login', "User {$event->user->name} logged in", $event->user);
            }
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user instanceof User) {
                ActivityLogger::log('logout', "User {$event->user->name} logged out", $event->user);
            }
        });

        Event::listen(\Illuminate\Auth\Events\Registered::class, function ($event) {
            if ($event->user instanceof User) {
                ActivityLogger::log('registered', "New user registered: {$event->user->name}", $event->user);
            }
        });
    }
}
