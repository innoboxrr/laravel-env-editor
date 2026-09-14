<?php

namespace Innoboxrr\EnvEditor\Tests\Feature;

use Illuminate\Auth\GenericUser;
use Illuminate\Http\UploadedFile;
use Innoboxrr\EnvEditor\Tests\Concerns\UsesTemporaryEnvironment;
use Innoboxrr\EnvEditor\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UploadTest extends TestCase
{
    use UsesTemporaryEnvironment;

    /**
     * Un PNG de 1x1.
     */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAMAASsJTYQAAAAASUVORK5CYII=';

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
    public function sube_un_env_como_copia_de_seguridad(): void
    {
        $original = $this->envContents();

        $this->postJson(route('env-editor.upload'), [
            'file' => $this->upload('.env', "APP_NAME=Uploaded\nFOO=bar\n"),
            'replace_current' => 'false',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertCount(1, $this->backups());
        $this->assertStringContainsString('FOO=bar', (string) file_get_contents($this->backups()[0]));
        $this->assertSame($original, $this->envContents());
    }

    #[Test]
    public function sube_un_archivo_de_texto_y_reemplaza_el_env(): void
    {
        $this->postJson(route('env-editor.upload'), [
            'file' => $this->upload('backup.txt', "APP_NAME=Replaced\n"),
            'replace_current' => 'true',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertSame("APP_NAME=Replaced\n", $this->envContents());
    }

    #[Test]
    public function rechaza_un_archivo_que_no_es_texto(): void
    {
        $original = $this->envContents();

        $this->postJson(route('env-editor.upload'), [
            'file' => $this->upload('logo.png', (string) base64_decode(self::PNG)),
            'replace_current' => 'true',
        ])->assertUnprocessable()->assertJsonValidationErrors('file');

        $this->assertSame($original, $this->envContents());
        $this->assertCount(0, $this->backups());
    }

    /**
     * Un archivo subido de verdad, no UploadedFile::fake(): el falso adivina el
     * tipo por el nombre, y ".env" no tiene extension conocida. Las reglas
     * mimetypes y mimes miran el contenido, que es lo que tiene que probarse.
     * El navegador suele mandar un .env como application/octet-stream.
     */
    private function upload(string $name, string $contents): UploadedFile
    {
        $path = $this->temporaryDirectory.DIRECTORY_SEPARATOR.'upload-'.bin2hex(random_bytes(4));
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, 'application/octet-stream', null, true);
    }
}
