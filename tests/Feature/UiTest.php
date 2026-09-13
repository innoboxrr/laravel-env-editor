<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Illuminate\Auth\GenericUser;
use Innoboxrr\EnvEditor\Tests\Concerns\UsesTemporaryEnvironment;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Lo que hace la interfaz para un usuario autenticado, contra una copia
 * temporal del .env.
 */
class UiTest extends TestCase
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

    #[Test]
    public function muestra_el_panel(): void
    {
        $this->get(route('env-editor.index'))
            ->assertOk()
            ->assertSee(trans('env-editor::env-editor.menuTitle'));
    }

    /**
     * Font Awesome Pro solo carga en los dominios dados de alta en un kit, y la
     * build de desarrollo de Vue avisa en consola y va mas lenta.
     */
    #[Test]
    public function el_panel_carga_font_awesome_free_y_vue_de_produccion(): void
    {
        $this->get(route('env-editor.index'))
            ->assertOk()
            ->assertDontSee('pro.fontawesome.com', false)
            ->assertSee('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', false)
            ->assertDontSee('vue/2.5.17/vue.js', false)
            ->assertSee('https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js', false);
    }

    #[Test]
    public function lista_las_claves_como_json(): void
    {
        $response = $this->getJson(route('env-editor.index'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $items = collect($response->json('items'));

        $this->assertSame('"Test env"', $items->firstWhere('key', 'APP_NAME')['value'] ?? null);
        $this->assertSame('stack', $items->firstWhere('key', 'LOG_CHANNEL')['value'] ?? null);
    }

    #[Test]
    public function agrega_actualiza_y_borra_una_clave(): void
    {
        $this->postJson(route('env-editor.key'), ['key' => 'FOO', 'value' => 'bar'])->assertOk();
        $this->assertStringContainsString('FOO=bar', $this->envContents());

        $this->patchJson(route('env-editor.key'), ['key' => 'FOO', 'value' => 'baz'])->assertOk();
        $this->assertStringContainsString('FOO=baz', $this->envContents());
        $this->assertStringNotContainsString('FOO=bar', $this->envContents());

        $this->deleteJson(route('env-editor.key'), ['key' => 'FOO'])->assertOk();
        $this->assertStringNotContainsString('FOO=', $this->envContents());
        $this->assertStringContainsString('APP_NAME="Test env"', $this->envContents());
    }

    #[Test]
    public function crea_restaura_y_borra_una_copia_de_seguridad(): void
    {
        $this->postJson(route('env-editor.createBackup'))->assertOk();

        $this->assertCount(1, $this->backups());
        $name = basename($this->backups()[0]);

        $this->getJson(route('env-editor.getBackups'))
            ->assertOk()
            ->assertJsonPath('items.0.name', $name);

        $this->patchJson(route('env-editor.key'), ['key' => 'APP_NAME', 'value' => 'Changed'])->assertOk();
        $this->assertStringContainsString('APP_NAME=Changed', $this->envContents());

        $this->postJson(route('env-editor.restoreBackup', ['filename' => $name]))->assertOk();
        $this->assertStringContainsString('APP_NAME="Test env"', $this->envContents());

        $this->deleteJson(route('env-editor.destroyBackup', ['filename' => $name]))->assertOk();
        $this->assertCount(0, $this->backups());
    }
}
