# PDF Scribe Bundle

A Symfony 7/8 bundle for generating PDFs using Chrome Headless. Provides excellent CSS support with the modern Chrome rendering engine.

## Installation

```bash
composer require vivutio/pdf-scribe-bundle
```

## Configuration

Create `config/packages/pdf_scribe.yaml`:

```yaml
pdf_scribe:
    binary_path: '%env(PDF_SCRIBE_BINARY)%'
    timeout: 120
    options:
        print-background: true
        no-pdf-header-footer: true
```

Add to `.env`:

```env
# macOS
PDF_SCRIBE_BINARY="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"

# Linux
PDF_SCRIBE_BINARY=/usr/bin/chromium-browser
```

## Usage

```php
use Vivutio\PdfScribeBundle\Contract\PdfGeneratorInterface;

class InvoiceController extends AbstractController
{
    public function download(Invoice $invoice, PdfGeneratorInterface $pdf): Response
    {
        $html = $this->renderView('invoice/pdf.html.twig', [
            'invoice' => $invoice,
        ]);

        $content = $pdf->fromHtml($html);

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice.pdf"',
        ]);
    }
}
```

## API

### PdfGeneratorInterface

- `fromHtml(string $html, array $options = []): string` - Generate PDF from HTML string
- `fromUrl(string $url, array $options = []): string` - Generate PDF from URL
- `saveFromHtml(string $html, string $outputPath, array $options = []): void` - Save PDF from HTML to file
- `saveFromUrl(string $url, string $outputPath, array $options = []): void` - Save PDF from URL to file

## Options

| Option | Type | Description |
|--------|------|-------------|
| `print-background` | bool | Print background graphics |
| `no-pdf-header-footer` | bool | Remove default header/footer |
| `landscape` | bool | Landscape orientation |
| `scale` | float | Scale factor (0.1 to 2.0) |

## CSS @page Rules

Control page size and margins via CSS in your HTML template:

```css
@page {
    size: A4;
    margin: 15mm;
}

@media print {
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
```

## License

MIT
