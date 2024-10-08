<?php

namespace Innoboxrr\EnvEditor\Helpers;

use Carbon\Carbon;
use Innoboxrr\EnvEditor\Dto\BackupObj;
use Innoboxrr\EnvEditor\EnvEditor;
use Innoboxrr\EnvEditor\Exceptions\EnvException;
use Innoboxrr\EnvEditor\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

class EnvFilesManager
{
    public function __construct(protected EnvEditor $envEditor, protected Filesystem $filesystem)
    {
        $this->makeBackupsDirectory();
    }

    /**
     * Get all Backup Files.
     *
     * @return Collection<int, BackupObj>
     */
    public function getAllBackUps(): Collection
    {
        $files = $this->filesystem->files($this->getBackupsDir());

        return (new Collection($files))
            ->map(fn (SplFileInfo $file): BackupObj => new BackupObj(
                $file->getFilename(),
                Carbon::parse($file->getCTime()),
                Carbon::parse($file->getMTime()),
                $file->getPath(),
                $file->getContents(),
                $this->envEditor->getFileContentManager()->getParsedFileContent($file->getFilename()),
            ))
            ->sortByDesc('createdAt');
    }

    /**
     * Used to create a backup of the current .env.
     * Will be assigned with the current timestamp.
     *
     * @throws EnvException
     */
    public function backUpCurrentEnv(): bool
    {
        return $this->filesystem->copy(
            $this->getFilePath(),
            $this->getBackupsDir($this->makeBackUpFileName())
        );
    }

    /**
     * Restore  the given backup-file.
     *
     * @throws EnvException
     */
    public function restoreBackup(string $fileName): bool
    {
        if ('' === $fileName) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.provideFileName'), 1);
        }
        $file = $this->getBackupsDir($fileName);

        return $this->filesystem->copy($file, $this->getFilePath());
    }

    /**
     * uploadBackup.
     */
    public function upload(UploadedFile $uploadedFile, bool $replaceCurrentEnv): File
    {
        return $replaceCurrentEnv ?
            $uploadedFile->move($this->getEnvDir(), $this->getEnvFileName()) :
            $uploadedFile->move($this->getBackupsDir(), $this->makeBackUpFileName());
    }

    /**
     * Delete the given backup-file.
     *
     * @throws EnvException
     */
    public function deleteBackup(string $fileName): bool
    {
        if ('' === $fileName) {
            throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.provideFileName'), 1);
        }
        $file = $this->getFilePath($fileName);

        return $this->filesystem->delete($file);
    }

    /**
     * Returns the full path of a backup file. If $fileName is empty return the path of the .env file.
     *
     * @throws EnvException
     */
    public function getFilePath(string $fileName = ''): string
    {
        $path = ('' === $fileName)
            ? $this->getEnvFileName()
            : $this->getBackupsDir($fileName);

        if ($this->filesystem->exists($path)) {
            return $path;
        }

        throw new EnvException(__(ServiceProvider::TRANSLATE_PREFIX.'exceptions.fileNotExists', ['name' => $path]), 0);
    }

    /**
     * Get the backup File Name.
     */
    protected function makeBackUpFileName(): string
    {
        return 'env_'.date('Y-m-d_His');
    }

    /**
     * Get the .env File Name.
     */
    protected function getEnvFileName(): string
    {
        return app()->environmentFilePath();
    }

    public function getBackupsDir(?string $path = null): string
    {
        return $this->envEditor->config('paths.backupDirectory').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    public function getEnvDir(?string $path = null): string
    {
        return dirname($this->getEnvFileName()).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Checks if Backups directory Exists and creates it.
     */
    public function makeBackupsDirectory(): void
    {
        $path = $this->getBackupsDir();
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->makeDirectory($path, 0755, true, true);
        }
    }
}
