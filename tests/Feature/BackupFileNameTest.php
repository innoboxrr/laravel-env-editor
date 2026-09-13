<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Illuminate\Auth\GenericUser;
use Innoboxrr\EnvEditor\Tests\Concerns\UsesTemporaryEnvironment;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Los errores del editor llegan a la interfaz como JSON con su mensaje, que es
 * lo que muestra envClient, y no como un 500 generico.
 */
class BackupFileNameTest extends TestCase
{
    use UsesTemporaryEnvironment;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->useTemporaryEnvironment($app);
        $app['config']->set('env-editor.route.enable', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(new GenericUser(['id' => 1]));
    }

    /**
     * {filename?} es opcional en la ruta, asi que la URL sin nombre existe.
     */
    #[Test]
    public function restaurar_o_borrar_sin_nombre_de_archivo_responde_400_con_mensaje(): void
    {
        $original = $this->envContents();
        $message = trans('env-editor::env-editor.exceptions.provideFileName');

        $this->postJson(route('env-editor.restoreBackup'))
            ->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', $message);

        $this->deleteJson(route('env-editor.destroyBackup'))
            ->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', $message);

        $this->assertSame($original, $this->envContents());
    }

    #[Test]
    public function agregar_una_clave_que_ya_existe_responde_400_con_mensaje(): void
    {
        $this->postJson(route('env-editor.key'), ['key' => 'APP_NAME', 'value' => 'Other'])
            ->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', trans('env-editor::env-editor.exceptions.keyAlreadyExists', ['name' => 'APP_NAME']));
    }
}
