<?php

namespace GertTimmerman\StatamicZapier;

use Statamic\Stache\Stache;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        'Statamic\Events\FormSubmitted' => [
            'GertTimmerman\StatamicZapier\Listeners\PushToWebhook',
        ]
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function boot()
    {
        parent::boot();

        // load publishables
        $this->bootPublishables();

        // load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'statamic-zapier');

        // load navigation
        $this->bootNavigation();

        // permissions
        Permission::group('statamic-zapier', 'Zapier Webhooks', function () {
            Permission::register('configure form zapier webhooks')->label('Configure Zapier Webhooks');
        });
    }

    public function bootPublishables(): static
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/statamic/zapier.php' => config_path('/statamic/zapier.php'),
            ], 'config');
        }

        return $this;
    }

    private function bootNavigation(): void
    {
        Nav::extend(function ($nav) {
            $nav->tools('Zapier Webhooks')
                ->can('configure form zapier webhooks')
                ->route('statamic-zapier.index')
                ->icon('form');
        });
    }
}