<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Innoboxrr\EnvEditor\Tests\Concerns\UsesTemporaryEnvironment;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Activar la interfaz con la configuracion por defecto no la abre a cualquiera.
 */
class RouteAccessTest extends TestCase
{
    use UsesTemporaryEnvironment;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->useTemporaryEnvironment($app);
        $app['config']->set('env-editor.route.enable', true);
    }

    /**
     * Laravel manda a los invitados a route('login'). Una aplicacion con
     * autenticacion la tiene; sin ella la redireccion falla con un 500, que
     * tampoco expone el .env pero no es lo que se prueba aqui.
     */
    protected function defineRoutes($router): void
    {
        $router->get('login', fn () => 'login')->name('login');
    }

    #[Test]
    public function el_middleware_por_defecto_exige_autenticacion(): void
    {
        $this->assertSame(['web', 'auth'], config('env-editor.route.middleware'));
        $this->assertNull(config('env-editor.route.gate'));
    }

    #[Test]
    public function un_invitado_no_puede_leer_ni_escribir_el_env(): void
    {
        $original = $this->envContents();

        $this->getJson(route('env-editor.index'))->assertUnauthorized();
        $this->getJson(route('env-editor.getBackups'))->assertUnauthorized();
        $this->postJson(route('env-editor.key'), ['key' => 'FOO', 'value' => 'bar'])->assertUnauthorized();
        $this->patchJson(route('env-editor.key'), ['key' => 'APP_NAME', 'value' => 'Hacked'])->assertUnauthorized();
        $this->deleteJson(route('env-editor.key'), ['key' => 'APP_NAME'])->assertUnauthorized();
        $this->postJson(route('env-editor.createBackup'))->assertUnauthorized();

        $this->get(route('env-editor.index'))->assertRedirect(route('login'));
        $this->get(route('env-editor.download'))->assertRedirect(route('login'));

        $this->assertSame($original, $this->envContents());
        $this->assertCount(0, $this->backups());
    }
}
