<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

final class AiGenerationException extends RuntimeException
{
    public static function invalidResponseShape(): self
    {
        return new self('A resposta da IA não contém os campos esperados.');
    }

    public static function invalidImageResponse(): self
    {
        return new self('A resposta de geração de imagem da IA não contém uma imagem válida.');
    }

    public static function unreachableUrl(?Throwable $previous = null): self
    {
        return new self('Não foi possível acessar o link informado para gerar o texto.', previous: $previous);
    }

    public static function blockedUrl(?Throwable $previous = null): self
    {
        return new self('O link informado não é permitido para geração de texto.', previous: $previous);
    }
}
