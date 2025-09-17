<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Validator::extend('step', function ($attribute, $value, $parameters, $validator) {
            $step = $parameters[0];
            return round($value / $step) === $value / $step;
        });

        Validator::replacer('step', function ($message, $attribute, $rule, $parameters) {
            $step = $parameters[0];
            return str_replace(':step', $step, $message);
        });
    }
}
