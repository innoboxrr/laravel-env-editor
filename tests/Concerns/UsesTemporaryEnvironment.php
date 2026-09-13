<?php

namespace Innoboxrr\EnvEditor\Tests\Concerns;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\After;

/**
 * Apunta el paquete a una copia del .env de ejemplo en un directorio temporal,
 * con las copias de seguridad al lado. Ningun test que lo use escribe en el .env
 * de la aplicacion ni en tests/fixtures.
 */
trait UsesTemporaryEnvironment
{
    protected string $temporaryDirectory;

    /**
     * Se llama desde getEnvironmentSetUp(): tiene que ocurrir antes de que se
     * resuelva el EnvEditor, que toma la configuracion al construirse.
     */
    protected function useTemporaryEnvironment($app): void
    {
        $this->temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'env-editor-'.bin2hex(random_bytes(6));

        mkdir($this->temporaryDirectory.DIRECTORY_SEPARATOR.'backups', 0777, true);
        copy(dirname(__DIR__).'/fixtures/.env.example', $this->temporaryDirectory.DIRECTORY_SEPARATOR.'.env');

        $app->useEnvironmentPath($this->temporaryDirectory);
        $app->loadEnvironmentFrom('.env');
        $app['config']->set('env-editor.paths.backupDirectory', $this->temporaryDirectory.DIRECTORY_SEPARATOR.'backups');
    }

    #[After]
    public function removeTemporaryEnvironment(): void
    {
        if (isset($this->temporaryDirectory)) {
            (new Filesystem())->deleteDirectory($this->temporaryDirectory);
        }
    }

    protected function envPath(): string
    {
        return $this->temporaryDirectory.DIRECTORY_SEPARATOR.'.env';
    }

    protected function envContents(): string
    {
        $this->assertSame($this->envPath(), $this->app->environmentFilePath(), 'El paquete no apunta al .env temporal.');

        return (string) file_get_contents($this->envPath());
    }

    /**
     * @return array<int, string>
     */
    protected function backups(): array
    {
        return glob($this->temporaryDirectory.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.'*') ?: [];
    }
}
