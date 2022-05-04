<?php

namespace MsAlvexx\LaravelPlans\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use MsAlvexx\LaravelPlans\Providers\FeatureRegistrar;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;

class PlanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBladeExtensions();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(FeatureRegistrar $featureLoader, Filesystem $filesystem)
    {
        if (isNotLumen()) {
            $this->publishes([
                __DIR__.'/../config/plans.php' => config_path('plans.php'),
            ], 'config');
        }

        if (app()->version() >= '5.5') {
            $this->registerMacroHelpers();
        }

        $featureLoader->registerFeatures();

        $this->app->singleton(FeatureRegistrar::class, function ($app) use ($featureLoader) {
            return $featureLoader;
        });
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            //plan
            $bladeCompiler->directive('plan', function ($plan) {
                return "<?php if(app('Company')->hasPlan({$plan})): ?>";
            });
            $bladeCompiler->directive('elseplan', function ($plan) {
                return "<?php elseif(app('Company')->hasPlan({$plan})): ?>";
            });
            $bladeCompiler->directive('endplan', function () {
                return '<?php endif; ?>';
            });

            //hasplan
            $bladeCompiler->directive('hasplan', function ($plan) {
                return "<?php if(app('Company')->hasPlan({$plan})): ?>";
            });
            $bladeCompiler->directive('endhasplan', function () {
                return '<?php endif; ?>';
            });

            //hasanyplan
            $bladeCompiler->directive('hasanyplan', function ($plan) {
                return "<?php if(app('Company')->hasAnyPlan({$plan})): ?>";
            });
            $bladeCompiler->directive('endhasanyplan', function () {
                return '<?php endif; ?>';
            });

            //unlessplan
            $bladeCompiler->directive('unlessplan', function ($plan) {
                return "<?php if(! app('Company')->hasAnyPlan({$plan})): ?>";
            });
            $bladeCompiler->directive('endunlessplan', function () {
                return '<?php endif; ?>';
            });


            //feature
            $bladeCompiler->directive('feature', function ($feature) {
                return "<?php if(app('Company')->hasFeature({$feature})): ?>";
            });
            $bladeCompiler->directive('elsefeature', function ($feature) {
                return "<?php elseif(app('Company')->hasFeature({$feature})): ?>";
            });
            $bladeCompiler->directive('endfeature', function () {
                return '<?php endif; ?>';
            });

            //hasfeature
            $bladeCompiler->directive('hasfeature', function ($feature) {
                return "<?php if(app('Company')->hasFeature({$feature})): ?>";
            });
            $bladeCompiler->directive('endhasfeature', function () {
                return '<?php endif; ?>';
            });

            //hasanyfeature
            $bladeCompiler->directive('hasanyfeature', function ($feature) {
                return "<?php if(app('Company')->hasAnyFeature({$feature})): ?>";
            });
            $bladeCompiler->directive('endhasanyfeature', function () {
                return '<?php endif; ?>';
            });

            //unlessfeature
            $bladeCompiler->directive('unlessfeature', function ($feature) {
                return "<?php if(! app('Company')->hasAnyFeature({$feature})): ?>";
            });
            $bladeCompiler->directive('endunlessfeature', function () {
                return '<?php endif; ?>';
            });
        });
    }

    protected function registerMacroHelpers()
    {
        Route::macro('plan', function ($plan = '') {
            $this->middleware("plan:$plan");

            return $this;
        });

        Route::macro('feature', function ($features = []) {
            if (! is_array($features)) {
                $features = [$features];
            }

            $features = implode('|', $features);

            $this->middleware("features:$features");

            return $this;
        });
    }
}
