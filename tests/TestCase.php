<?php

namespace Innoboxrr\EnvEditor\Tests;

use Innoboxrr\EnvEditor\Facades\EnvEditor;
use Innoboxrr\EnvEditor\ServiceProvider;
use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        copy(self::getTestFile(true), self::getTestPath().'/copy');
    }

    protected function tearDown(): void
    {
        copy(self::getTestPath().'/copy', self::getTestFile(true));
        unlink(self::getTestPath().'/copy');
        parent::tearDown();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $key = 'base64:'.base64_encode(
            Encrypter::generateKey('AES-256-CBC')
        );

        $app['config']->set('app.key', $key);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'env-editor' => EnvEditor::class,
        ];
    }

    protected static function getTestPath(): string
    {
        return __DIR__.'/fixtures';
    }

    protected static function getTestFile(bool $fullPath = false): string
    {
        $file = '.env.example';

        return $fullPath
            ? static::getTestPath().DIRECTORY_SEPARATOR.$file
            : $file;
    }
}
