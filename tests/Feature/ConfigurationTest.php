<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ConfigurationTest extends TestCase
{
    /**
     * Instalar el paquete no puede exponer el .env: la interfaz solo existe si
     * la aplicacion la activa.
     */
    #[Test]
    public function las_rutas_no_se_registran_por_defecto(): void
    {
        $this->assertFalse(config('env-editor.route.enable'));

        $names = ['index', 'key', 'clearConfigCache', 'getBackups', 'createBackup', 'restoreBackup', 'destroyBackup', 'download', 'upload'];

        foreach ($names as $name) {
            $this->assertFalse(Route::has('env-editor.'.$name), "La ruta env-editor.{$name} no deberia existir.");
        }

        $this->get('/env-editor')->assertNotFound();
        $this->getJson('/env-editor')->assertNotFound();
    }
}
