<?php

declare(strict_types=1);

namespace Vivutio\PdfScribeBundle\Service;

use Symfony\Component\Process\Process;
use Vivutio\PdfScribeBundle\Contract\PdfGeneratorInterface;
use Vivutio\PdfScribeBundle\Exception\PdfGenerationException;

class PdfGeneratorService implements PdfGeneratorInterface
{
    private bool $binaryValidated = false;

    /**
     * @param array<string, mixed> $defaultOptions
     */
    public function __construct(
        private readonly string $binaryPath,
        private readonly int $timeout,
        private readonly array $defaultOptions = [],
    ) {
    }

    public function fromHtml(string $html, array $options = []): string
    {
        $this->validateBinary();

        $inputFile = $this->createTempFile($html, 'html');
        $outputFile = $this->createTempFilePath('pdf');

        try {
            $this->generate($inputFile, $outputFile, $options);

            return $this->readAndDeleteFile($outputFile);
        } finally {
            $this->deleteFile($inputFile);
            $this->deleteFile($outputFile);
        }
    }

    public function fromUrl(string $url, array $options = []): string
    {
        $this->validateBinary();

        $outputFile = $this->createTempFilePath('pdf');

        try {
            $this->generate($url, $outputFile, $options);

            return $this->readAndDeleteFile($outputFile);
        } finally {
            $this->deleteFile($outputFile);
        }
    }

    public function saveFromHtml(string $html, string $outputPath, array $options = []): void
    {
        $this->validateBinary();

        $inputFile = $this->createTempFile($html, 'html');

        try {
            $this->generate($inputFile, $outputPath, $options);
        } finally {
            $this->deleteFile($inputFile);
        }
    }

    public function saveFromUrl(string $url, string $outputPath, array $options = []): void
    {
        $this->validateBinary();

        $this->generate($url, $outputPath, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function generate(string $input, string $output, array $options): void
    {
        $command = $this->buildCommand($input, $output, $options);

        $process = new Process($command);
        $process->setTimeout($this->timeout);
        $process->run();

        // Chrome returns exit code 0 on success
        if (!$process->isSuccessful()) {
            throw PdfGenerationException::processError(
                $process->getErrorOutput() ?: $process->getOutput(),
                $process->getExitCode() ?? 1,
            );
        }

        if (!file_exists($output)) {
            throw PdfGenerationException::outputFileNotCreated($output);
        }
    }

    /**
     * Build Chrome headless command for PDF generation.
     *
     * @param array<string, mixed> $options
     *
     * @return list<string>
     */
    private function buildCommand(string $input, string $output, array $options): array
    {
        $mergedOptions = array_merge($this->defaultOptions, $options);

        // For local HTML files, convert to file:// URL
        if (file_exists($input)) {
            $input = 'file://' . $input;
        }

        $command = [
            $this->binaryPath,
            '--headless',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-software-rasterizer',
            '--disable-dev-shm-usage',
            '--run-all-compositor-stages-before-draw',
            '--print-to-pdf=' . $output,
        ];

        // Handle Chrome-specific options
        if (!empty($mergedOptions['no-pdf-header-footer']) || !isset($mergedOptions['no-pdf-header-footer'])) {
            $command[] = '--no-pdf-header-footer';
        }

        // Paper size (default A4)
        if (!empty($mergedOptions['paper-width']) && !empty($mergedOptions['paper-height'])) {
            // Custom size in inches
        } elseif (!empty($mergedOptions['format'])) {
            // Named format like 'A4', 'Letter' - handled by @page CSS
        }

        // Print background (default true for Chrome)
        if (isset($mergedOptions['print-background']) && $mergedOptions['print-background']) {
            $command[] = '--print-background';
        }

        // Landscape mode
        if (!empty($mergedOptions['landscape'])) {
            $command[] = '--landscape';
        }

        // Scale
        if (!empty($mergedOptions['scale'])) {
            $command[] = '--scale=' . $mergedOptions['scale'];
        }

        // Virtual time budget for JavaScript execution (milliseconds)
        if (!empty($mergedOptions['virtual-time-budget'])) {
            $command[] = '--virtual-time-budget=' . $mergedOptions['virtual-time-budget'];
        }

        // Input URL/file must be last
        $command[] = $input;

        return $command;
    }

    private function validateBinary(): void
    {
        if ($this->binaryValidated) {
            return;
        }

        if (!file_exists($this->binaryPath)) {
            throw PdfGenerationException::binaryNotFound($this->binaryPath);
        }

        if (!is_executable($this->binaryPath)) {
            throw PdfGenerationException::binaryNotExecutable($this->binaryPath);
        }

        $this->binaryValidated = true;
    }

    private function createTempFile(string $content, string $extension): string
    {
        $path = $this->createTempFilePath($extension);

        if (file_put_contents($path, $content) === false) {
            throw PdfGenerationException::tempFileError('Failed to write temporary file');
        }

        return $path;
    }

    private function createTempFilePath(string $extension): string
    {
        return sys_get_temp_dir() . '/pdf_scribe_' . uniqid() . '.' . $extension;
    }

    private function readAndDeleteFile(string $path): string
    {
        if (!file_exists($path)) {
            throw PdfGenerationException::outputFileNotCreated($path);
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw PdfGenerationException::tempFileError('Failed to read output file');
        }

        $this->deleteFile($path);

        return $content;
    }

    private function deleteFile(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
