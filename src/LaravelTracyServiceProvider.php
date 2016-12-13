<?php

namespace Recca0120\LaravelTracy;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Recca0120\LaravelTracy\Exceptions\Handler;
use Recca0120\LaravelTracy\Middleware\Dispatch;
use Recca0120\Terminal\TerminalServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Recca0120\LaravelTracy\Session\StoreWrapper;

class LaravelTracyServiceProvider extends ServiceProvider
{
    /**
     * boot.
     *
     * @method boot
     *
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     * @param \Illuminate\View\Compilers\BladeCompiler $bladeCompiler
     */
    public function boot(Kernel $kernel, BladeCompiler $bladeCompiler)
    {
        if ($this->app->runningInConsole() === true) {
            $this->publishes([
                __DIR__.'/../config/tracy.php' => $this->app->configPath().'/tracy.php',
            ], 'config');

            return;
        }

        if ($this->app['config']['tracy']['enabled'] === true) {
            $this->app->extend(ExceptionHandler::class, function ($exceptionHandler, $app) {
                return $app->make(Handler::class, [
                    'exceptionHandler' => $exceptionHandler,
                ]);
            });

            $kernel->prependMiddleware(Dispatch::class);
        }

        $bladeCompiler->directive('bdump', function ($expression) {
            return "<?php \Tracy\Debugger::barDump({$expression}); ?>";
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tracy.php', 'tracy');

        $this->app->singleton(Debugbar::class, function ($app) {
            $config = Arr::get($app['config'], 'tracy', []);

            return new Debugbar($config, $app['request'], $app);
        });

        $this->app->singleton(StoreWrapper::class, StoreWrapper::class);
        $this->app->singleton(BlueScreen::class, BlueScreen::class);

        if ($this->app['config']['tracy.panels.terminal'] === true) {
            $this->app->register(TerminalServiceProvider::class);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ExceptionHandler::class];
    }
}
