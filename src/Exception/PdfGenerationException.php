<?php

declare(strict_types=1);

namespace Vivutio\PdfScribeBundle\Exception;

use RuntimeException;

class PdfGenerationException extends RuntimeException
{
    public static function binaryNotFound(string $path): self
    {
        return new self(sprintf('Chrome/Chromium binary not found at "%s"', $path));
    }

    public static function binaryNotExecutable(string $path): self
    {
        return new self(sprintf('Chrome/Chromium binary at "%s" is not executable', $path));
    }

    public static function processError(string $error, int $exitCode): self
    {
        return new self(sprintf('PDF generation failed (exit code %d): %s', $exitCode, $error));
    }

    public static function outputFileNotCreated(string $path): self
    {
        return new self(sprintf('PDF output file was not created at "%s"', $path));
    }

    public static function tempFileError(string $message): self
    {
        return new self(sprintf('Temporary file error: %s', $message));
    }
}
