<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Innoboxrr\EnvEditor\Tests\Concerns\UsesTemporaryEnvironment;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Con env-editor.route.gate, estar autenticado no basta: hace falta la habilidad.
 */
class GateTest extends TestCase
{
    use UsesTemporaryEnvironment;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->useTemporaryEnvironment($app);
        $app['config']->set('env-editor.route.enable', true);
        $app['config']->set('env-editor.route.gate', 'manage-env');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('manage-env', fn (Authenticatable $user): bool => 1 === $user->getAuthIdentifier());
    }

    #[Test]
    public function un_usuario_sin_la_habilidad_recibe_403_y_el_env_no_cambia(): void
    {
        $this->actingAs(new GenericUser(['id' => 2]));
        $original = $this->envContents();

        $this->getJson(route('env-editor.index'))->assertForbidden();
        $this->postJson(route('env-editor.key'), ['key' => 'FOO', 'value' => 'bar'])->assertForbidden();
        $this->postJson(route('env-editor.createBackup'))->assertForbidden();

        $this->assertSame($original, $this->envContents());
        $this->assertCount(0, $this->backups());
    }

    #[Test]
    public function un_usuario_con_la_habilidad_entra(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));

        $this->getJson(route('env-editor.index'))->assertOk();
        $this->postJson(route('env-editor.key'), ['key' => 'FOO', 'value' => 'bar'])->assertOk();

        $this->assertStringContainsString('FOO=bar', $this->envContents());
    }

    #[Test]
    public function un_invitado_recibe_401_antes_que_403(): void
    {
        $this->getJson(route('env-editor.index'))->assertUnauthorized();
    }
}
