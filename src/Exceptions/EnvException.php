<?php

namespace Innoboxrr\EnvEditor\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnvException extends \Exception
{
    public function __toString(): string
    {
        return self::class.":[{$this->code}]: {$this->message}\n";
    }

    /**
     * En una peticion JSON, como las de la interfaz, el error llega con su
     * mensaje y un 400, igual que las respuestas fallidas del controlador: la
     * interfaz muestra data.message. En el resto, Laravel la trata como a
     * cualquier otra excepcion.
     */
    public function render(Request $request): ?JsonResponse
    {
        if (!$request->expectsJson()) {
            return null;
        }

        return new JsonResponse([
            'message' => $this->getMessage(),
            'success' => false,
        ], 400);
    }
}
