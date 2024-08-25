<?php

namespace Innoboxrr\EnvEditor\Exceptions;

class EnvException extends \Exception
{
    public function __toString(): string
    {
        return self::class.":[{$this->code}]: {$this->message}\n";
    }
}
