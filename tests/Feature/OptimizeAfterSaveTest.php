<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Illuminate\Support\Facades\Bus;
use Innoboxrr\EnvEditor\Facades\EnvEditor;
use Innoboxrr\EnvEditor\Jobs\OptimizeApplication;
use Innoboxrr\EnvEditor\Tests\Concerns\UsesTemporaryEnvironment;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OptimizeAfterSaveTest extends TestCase
{
    use UsesTemporaryEnvironment;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->useTemporaryEnvironment($app);
    }

    #[Test]
    public function sin_configuracion_cacheada_guardar_no_lanza_optimize(): void
    {
        Bus::fake();
        $this->app->instance('config_loaded_from_cache', false);

        EnvEditor::editKey('APP_NAME', 'Changed');

        $this->assertStringContainsString('APP_NAME=Changed', $this->envContents());
        Bus::assertNotDispatched(OptimizeApplication::class);
    }

    #[Test]
    public function con_configuracion_cacheada_guardar_programa_optimize(): void
    {
        Bus::fake();
        $this->app->instance('config_loaded_from_cache', true);

        EnvEditor::editKey('APP_NAME', 'Changed');

        Bus::assertDispatched(OptimizeApplication::class);
    }
}
