<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Process;
use thiagoalessio\TesseractOCR\Option;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Throwable;

class OcrService
{
    public function extractFromPath(string $absolutePath, string $extension): string
    {
        $extension = strtolower($extension);

        if ($extension === 'pdf') {
            $nativeText = $this->extractNativePdfText($absolutePath);

            if ($nativeText !== '') {
                return $nativeText;
            }

            return $this->extractScannedPdfText($absolutePath);
        }

        return $this->extractImageText($absolutePath);
    }

    private function extractNativePdfText(string $absolutePath): string
    {
        try {
            $parser = new Parser;
            $text = $parser->parseFile($absolutePath)->getText();

            return $this->normalizeText($text);
        } catch (Throwable) {
            return '';
        }
    }

    private function extractScannedPdfText(string $absolutePath): string
    {
        $imagePaths = $this->convertPdfToImages($absolutePath);

        if ($imagePaths === []) {
            try {
                return $this->extractWithTesseract($absolutePath);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    'PDF escaneado detectado. Instale Imagick + Ghostscript ou envie a imagem em PNG/JPG.',
                    previous: $exception
                );
            }
        }

        try {
            $chunks = [];

            foreach ($imagePaths as $imagePath) {
                $chunks[] = $this->extractWithTesseract($imagePath);
            }

            return $this->normalizeText(implode(PHP_EOL.PHP_EOL, array_filter($chunks)));
        } finally {
            File::delete($imagePaths);

            if (isset($imagePaths[0])) {
                File::deleteDirectory(dirname($imagePaths[0]));
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function convertPdfToImages(string $absolutePath): array
    {
        if (! extension_loaded('imagick') || ! class_exists(\Imagick::class)) {
            return [];
        }

        $resolution = max(72, (int) config('ocr.pdf_resolution', 200));
        $maxPages = max(1, (int) config('ocr.max_pdf_pages', 5));
        $tempDirectory = storage_path('app/tmp/ocr-'.Str::uuid());
        File::ensureDirectoryExists($tempDirectory);

        try {
            $imagick = new \Imagick;
            $imagick->setResolution($resolution, $resolution);
            $imagick->readImage($absolutePath);

            $imagePaths = [];
            $index = 0;

            foreach ($imagick as $page) {
                if ($index >= $maxPages) {
                    break;
                }

                $page->setImageFormat('png');
                $pagePath = "{$tempDirectory}/page-{$index}.png";
                $page->writeImage($pagePath);
                $imagePaths[] = $pagePath;
                $index++;
            }

            $imagick->clear();
            $imagick->destroy();

            return $imagePaths;
        } catch (Throwable) {
            File::deleteDirectory($tempDirectory);

            return [];
        }
    }

    private function extractImageText(string $absolutePath): string
    {
        return $this->extractWithTesseract($absolutePath);
    }

    private function extractWithTesseract(string $absolutePath): string
    {
        $ocr = new TesseractOCR($absolutePath);
        $binary = $this->resolveTesseractBinary();
        $tessdataPath = $this->resolveTessdataPath();
        $language = (string) config('ocr.language', 'por');

        if ($binary === '') {
            throw new RuntimeException(
                'Tesseract OCR nao encontrado. Instale o Tesseract e configure OCR_TESSERACT_BINARY no .env.'
            );
        }

        $ocr->executable($binary);

        if ($tessdataPath !== '' && is_dir($tessdataPath)) {
            $ocr->command->options[] = Option::tessdataDir($tessdataPath);
        }

        if ($language !== '') {
            $ocr->command->options[] = Option::lang($language);
        }

        $text = $this->normalizeText($ocr->run());

        if ($text === '') {
            throw new RuntimeException('Nao foi possivel extrair texto do arquivo enviado.');
        }

        return $text;
    }

    private function resolveTesseractBinary(): string
    {
        $configuredBinary = trim((string) config('ocr.tesseract_binary', ''));
        $candidates = array_filter([
            $configuredBinary,
            'tesseract',
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        ]);

        foreach ($candidates as $candidate) {
            $process = new Process([$candidate, '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                return $candidate;
            }
        }

        return '';
    }

    private function resolveTessdataPath(): string
    {
        $configuredPath = trim((string) config('ocr.tessdata_path', ''));

        if ($configuredPath !== '' && is_dir($configuredPath)) {
            return $configuredPath;
        }

        $projectTessdataPath = storage_path('app/tessdata');

        if (is_dir($projectTessdataPath)) {
            return $projectTessdataPath;
        }

        return '';
    }

    private function normalizeText(string $text): string
    {
        $text = preg_replace('/\R{3,}/u', PHP_EOL.PHP_EOL, trim($text));

        return trim((string) $text);
    }
}
