<?php

namespace Anwoon\BlueprintGraphqlAddon;

use Anwoon\BlueprintGraphqlAddon\Generator\GraphqlGenerator;
use Blueprint\Blueprint;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Naoray\BlueprintNovaAddon\BlueprintNovaAddonServiceProvider;

class BlueprintGraphqlAddonServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            'command.blueprint.build',
            GraphqlGenerator::class,
            Blueprint::class,
        ];
    }

    public function register()
    {
        $this->app->singleton(GraphqlGenerator::class, function ($app) {
            return new GraphqlGenerator($app['files']);
        });

        $this->app->extend(Blueprint::class, function ($blueprint, $app) {
            $blueprint->registerGenerator($app[GraphqlGenerator::class]);

            return $blueprint;
        });
    }
}
