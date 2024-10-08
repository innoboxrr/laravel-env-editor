<?php

namespace Innoboxrr\EnvEditor\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @implements Arrayable<string, scalar>
 */
class EntryObj implements \JsonSerializable, Arrayable
{
    /**
     * @param int|string|null $value
     */
    public function __construct(
        public readonly string $key,
        protected mixed $value,
        public readonly int $group,
        public readonly int $index,
        protected bool $isSeparator = false
    ) {
    }

    public static function parseEnvLine(string $line, int $group, int $index): self
    {
        $entry = explode('=', $line, 2);
        $isSeparator = 1 === count($entry);

        return new self(Arr::get($entry, 0), Arr::get($entry, 1), $group, $index, $isSeparator);
    }

    public static function makeKeysSeparator(int $groupIndex, int $index): self
    {
        return new self('', '', $groupIndex, $index, true);
    }

    public function getAsEnvLine(): string
    {
        return $this->isSeparator() ? '' : "$this->key=$this->value";
    }

    public function isSeparator(): bool
    {
        return $this->isSeparator;
    }

    /**
     * @return int|string|mixed|null
     */
    public function getValue(mixed $default = null): mixed
    {
        return $this->value ?: $default;
    }

    /**
     * @param int|string|mixed|null $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return array{key:string, value: int|string|null, group:int, index:int , isSeparator:bool}
     */
    public function toArray(): array
    {
        /** @var array{key:string, value: int|string|null, group:int, index:int , isSeparator:bool} $result */
        $result = get_object_vars($this);

        return $result;
    }

    /**
     * @return array{key:string, value: int|string|null, group:int, index:int , isSeparator:bool}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
