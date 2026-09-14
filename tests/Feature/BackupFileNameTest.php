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
    public function un_nombre_que_sale_del_directorio_de_copias_se_rechaza(): void
    {
        $original = $this->envContents();

        // Un archivo junto al directorio de copias, fuera de el.
        $outside = $this->temporaryDirectory.DIRECTORY_SEPARATOR.'outside.txt';
        file_put_contents($outside, 'FOO=outside');

        foreach (['..%5Coutside.txt', '..%5C.env', '..'] as $name) {
            $this->deleteJson('/env-editor/files/destroy-backup/'.$name)
                ->assertStatus(400)
                ->assertJsonPath('success', false);

            $this->postJson('/env-editor/files/restore-backup/'.$name)
                ->assertStatus(400)
                ->assertJsonPath('success', false);

            $this->getJson('/env-editor/files/download/'.$name)
                ->assertStatus(400)
                ->assertJsonPath('success', false);
        }

        $this->assertFileExists($outside);
        $this->assertSame('FOO=outside', file_get_contents($outside));
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
