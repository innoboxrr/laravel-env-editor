<?php

namespace Innoboxrr\EnvEditor;

use Innoboxrr\EnvEditor\Dto\BackupObj;
use Innoboxrr\EnvEditor\Dto\EntryObj;
use Innoboxrr\EnvEditor\Exceptions\EnvException;
use Innoboxrr\EnvEditor\Helpers\EnvFileContentManager;
use Innoboxrr\EnvEditor\Helpers\EnvFilesManager;
use Innoboxrr\EnvEditor\Helpers\EnvKeysManager;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class Settings.
 */
class EnvEditor
{
    protected EnvKeysManager $keysManager;

    protected EnvFilesManager $filesManager;

    protected EnvFileContentManager $fileContentManager;

    public function __construct(protected Repository $configRepository, Filesystem $filesystem)
    {
        $this->keysManager = new EnvKeysManager($this);
        $this->filesManager = new EnvFilesManager($this, $filesystem);
        $this->fileContentManager = new EnvFileContentManager($this, $filesystem);
    }

    /**
     * Parse the .env Contents.
     *
     * @return Collection<int, EntryObj>
     *
     * @throws EnvException
     */
    public function getEnvFileContent(string $fileName = ''): Collection
    {
        return $this->getFileContentManager()->getParsedFileContent($fileName);
    }

    /**
     * Check if key Exist in Current env.
     */
    public function keyExists(string $key): bool
    {
        return $this->getKeysManager()->has($key);
    }

    /**
     * Add the  Key  on the Current Env.
     */
    public function getKey(string $key, mixed $default = null): float|bool|int|string|null
    {
        return $this->getKeysManager()->get($key, $default);
    }

    /**
     * Add the  Key  on the Current Env.
     *
     * @param array<string, int|string> $options
     *
     * @throws EnvException
     */
    public function addKey(string $key, mixed $value, array $options = []): bool
    {
        return $this->getKeysManager()->add($key, $value, $options);
    }

    /**
     * Edits the Given Key  env.
     *
     * @throws EnvException
     */
    public function editKey(string $keyToChange, mixed $newValue): bool
    {
        return $this->getKeysManager()->edit($keyToChange, $newValue);
    }

    /**
     * Deletes the Given Key form env.
     *
     * @throws EnvException
     */
    public function deleteKey(string $key): bool
    {
        return $this->getKeysManager()->delete($key);
    }

    /**
     * Get all Backup Files.
     *
     * @return Collection<int, BackupObj>
     */
    public function getAllBackUps(): Collection
    {
        return $this->getFilesManager()->getAllBackUps();
    }

    /**
     * upload Backup.
     */
    public function upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv): File
    {
        return $this->getFilesManager()->upload($uploadedFile, $replaceCurrentEnv);
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @throws EnvException
     */
    public function backUpCurrent(): bool
    {
        return $this->getFilesManager()->backUpCurrentEnv();
    }

    /**
     * Returns the full path of a backup file. If $fileName is empty return the path of the .env file.
     *
     * @throws EnvException
     */
    public function getFilePath(string $fileName = ''): string
    {
        return $this->getFilesManager()->getFilePath($fileName);
    }

    /**
     * Delete the given backup-file.
     *
     * @throws EnvException
     */
    public function deleteBackup(string $fileName): bool
    {
        return $this->getFilesManager()->deleteBackup($fileName);
    }

    /**
     * Restore  the given backup-file.
     *
     * @throws EnvException
     */
    public function restoreBackUp(string $fileName): bool
    {
        return $this->getFilesManager()->restoreBackup($fileName);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->configRepository->get($key, $default);
    }

    public function getKeysManager(): EnvKeysManager
    {
        return $this->keysManager;
    }

    public function getFilesManager(): EnvFilesManager
    {
        return $this->filesManager;
    }

    public function getFileContentManager(): EnvFileContentManager
    {
        return $this->fileContentManager;
    }
}
