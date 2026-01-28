<?php

declare(strict_types=1);

namespace Vivutio\PdfScribeBundle\Contract;

use Vivutio\PdfScribeBundle\Exception\PdfGenerationException;

interface PdfGeneratorInterface
{
    /**
     * Generate PDF from HTML string.
     *
     * @param array<string, mixed> $options Override default options
     *
     * @return string PDF binary content
     *
     * @throws PdfGenerationException
     */
    public function fromHtml(string $html, array $options = []): string;

    /**
     * Generate PDF from URL.
     *
     * @param array<string, mixed> $options Override default options
     *
     * @return string PDF binary content
     *
     * @throws PdfGenerationException
     */
    public function fromUrl(string $url, array $options = []): string;

    /**
     * Generate PDF from HTML and save to file.
     *
     * @param array<string, mixed> $options Override default options
     *
     * @throws PdfGenerationException
     */
    public function saveFromHtml(string $html, string $outputPath, array $options = []): void;

    /**
     * Generate PDF from URL and save to file.
     *
     * @param array<string, mixed> $options Override default options
     *
     * @throws PdfGenerationException
     */
    public function saveFromUrl(string $url, string $outputPath, array $options = []): void;
}
