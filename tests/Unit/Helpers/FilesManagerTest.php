<?php

namespace Innoboxrr\EnvEditor\Tests\Unit\Helpers;

use Innoboxrr\EnvEditor\Dto\EntryObj;
use Innoboxrr\EnvEditor\EnvEditor;
use Innoboxrr\EnvEditor\Exceptions\EnvException;
use Innoboxrr\EnvEditor\Helpers\EnvFilesManager;
use Innoboxrr\EnvEditor\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('helpers')]
class FilesManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->cleanBackUpDir();
        parent::tearDown();
    }

    /**
     * Test makeBackupsDirectory method.
     */
    #[Test]
    public function constructor_calls_make_backups_directory_method(): void
    {
        $classname = EnvFilesManager::class;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('makeBackupsDirectory');

        // now call the constructor
        $reflectedClass = new \ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();

        $envEditorMock = \Mockery::mock(EnvEditor::class);
        $constructor->invoke($mock, $envEditorMock, $this->app->make(Filesystem::class));
    }

    /**
     * Test makeBackupsDirectory method.
     */
    #[Test]
    public function backup_dir_is_created(): void
    {
        $path = $this->getEnvFilesManager()->getBackupsDir();
        $this->createAndTestPath($path);
    }

    /**
     * Test makeBackupsDirectory method.
     */
    #[Test]
    public function get_env_dir_exists(): void
    {
        $path = $this->getEnvFilesManager()->getEnvDir();
        $this->createAndTestPath($path);
    }

    #[Test]
    public function get_backups_dir_can_return_file(): void
    {
        $path = $this->getEnvFilesManager()->getBackupsDir();
        $filename = 'test.tmp';
        $filePath = $path.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($filePath, 'dummy');

        $filePath1 = $this->getEnvFilesManager()->getBackupsDir($filename);
        $this->assertTrue(file_exists($filePath1));
        unlink($filePath);
    }

    #[Test]
    public function get_env_dir_can_return_file(): void
    {
        $path = $this->getEnvFilesManager()->getEnvDir();
        $filename = 'test.tmp';
        $filePath = $path.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($filePath, 'dummy');

        $filePath1 = $this->getEnvFilesManager()->getEnvDir($filename);
        $this->assertTrue(file_exists($filePath1));
        unlink($filePath);
    }

    #[Test]
    public function get_all_back_ups_returns_all_files(): void
    {
        $manager = $this->getEnvFilesManager();
        $file1 = $manager->getBackupsDir('test.tmp');
        $file2 = $manager->getBackupsDir('test2.tmp');
        file_put_contents($file1, 'dummy');
        file_put_contents($file2, 'dummy');

        $backUps = $manager->getAllBackUps();
        $this->assertEquals(2, $backUps->count());

        unlink($file1);
        unlink($file2);
    }

    #[Test]
    public function back_up_current_env_works_and_returns_bool(): void
    {
        $fileName = 'test.tmp';
        $this->app->loadEnvironmentFrom($fileName);

        $content = time().'_dummy';
        $manager = $this->getEnvFilesManager();
        $file = $manager->getEnvDir($fileName);
        file_put_contents($file, $content);

        // Check CurrentEnv
        $currentEnv = $manager->getFilePath();

        $this->assertTrue(file_exists($currentEnv));
        $this->assertEquals(file_get_contents($currentEnv), $content);

        $result = $manager->backUpCurrentEnv();
        $this->assertTrue($result);

        $backUps = $manager->getAllBackUps();

        $this->assertEquals(1, $backUps->count());
        $backup = $backUps->first();
        $this->assertInstanceOf(Collection::class, $backup->entries);
        $this->assertInstanceOf(EntryObj::class, $backup->entries->first());
        $this->assertEquals($backup->rawContent, $content);

        unlink($file);
    }

    #[Test]
    public function restore_backup_works_and_returns_bool(): void
    {
        $this->app->loadEnvironmentFrom('.env.example');
        $manager = $this->getEnvFilesManager();
        // place a dummy env file
        file_put_contents($this->app->environmentFile(), '');

        $fileName = time().'_test.tmp';
        $content = time().'_dummy';
        $file = $manager->getBackupsDir($fileName);
        file_put_contents($file, $content);

        $result = $manager->restoreBackup($fileName);
        $this->assertTrue($result);

        $currentEnv = $manager->getFilePath();
        $this->assertEquals(file_get_contents($currentEnv), $content);

        unlink($file);
    }

    #[Test]
    public function restore_backup_wrong_backup(): void
    {
        $manager = $this->getEnvFilesManager();

        self::expectException(EnvException::class);
        self::expectExceptionMessage('You have to provide a FileName !!!');
        $manager->restoreBackup('');
    }

    #[Test]
    public function delete_backup_works_and_returns_bool(): void
    {
        $fileName = time().'_test.tmp';
        $manager = $this->getEnvFilesManager();
        $file = $manager->getBackupsDir($fileName);
        file_put_contents($file, 'dummy');

        $result = $manager->deleteBackup($fileName);
        $this->assertTrue($result);

        $this->assertFalse(file_exists($file));
    }

    #[Test]
    public function delete_backup_throws_exception_if_empty_sting_given(): void
    {
        $manager = $this->getEnvFilesManager();
        $this->expectException(EnvException::class);
        $this->expectExceptionMessage('You have to provide a FileName !!!');
        $manager->deleteBackup('');
    }

    private function createAndTestPath(string $path): void
    {
        $path = realpath($path);
        $this->assertNotFalse($path);
        $filename = tempnam($path, 'test') ?: throw new \RuntimeException("Couldn't create file");
        $this->assertEquals($filename, realpath($filename));
        unlink($filename);
    }

    private function cleanBackUpDir(): void
    {
        (new Filesystem())->cleanDirectory($this->getEnvFilesManager()->getBackupsDir());
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function getEnvFilesManager(array $config = []): EnvFilesManager
    {
        $envEditor = new EnvEditor(
            new Repository($config ?: $this->app['config']->get('env-editor')),
            new Filesystem()
        );

        return $envEditor->getFilesManager();
    }
}
