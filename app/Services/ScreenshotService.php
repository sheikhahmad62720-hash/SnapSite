<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Exception;

class ScreenshotService
{
    private string $tempDir;

    private array $blockedHosts = [
        'localhost',
        '127.0.0.1',
        '0.0.0.0',
        '::1',
    ];

    private array $blockedSchemes = [
        'file://',
        'javascript:',
        'data:',
        'ftp://',
    ];

    public function __construct()
    {
        $this->tempDir = storage_path('app/temp/screenshots');

        if (!File::isDirectory($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true, true);
        }
    }

    public function generate(string $url, int $width, int $height, string $screenshotType, string $format): array
    {
        $this->validateUrl($url);
        $this->validateDimensions($width, $height);

        $filename = Str::uuid() . '.' . $format;
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;

        try {
            $chromiumPath = $this->findChromiumPath();
            $nodeScript = $this->buildNodeScript($url, $width, $height, $screenshotType, $format, $filePath);

            $tempScript = $this->tempDir . DIRECTORY_SEPARATOR . Str::uuid() . '.js';
            File::put($tempScript, $nodeScript);

            $command = escapeshellcmd("node " . $tempScript . " 2>&1");
            $output = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            File::delete($tempScript);

            if ($exitCode !== 0) {
                $errorOutput = implode("\n", $output);
                throw new Exception("Screenshot generation failed: " . $errorOutput);
            }

            if (!File::exists($filePath)) {
                throw new Exception("Screenshot file was not created.");
            }

            $fileSize = File::size($filePath);

            return [
                'filename' => $filename,
                'path' => $filePath,
                'width' => $width,
                'height' => $height,
                'format' => $format,
                'file_size' => $fileSize,
                'download_url' => route('screenshot.download', ['filename' => $filename]),
            ];
        } catch (Exception $e) {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            throw $e;
        }
    }

    public function getFilePath(string $filename): ?string
    {
        $filename = basename($filename);
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($filePath)) {
            return $filePath;
        }

        return null;
    }

    public function deleteFile(string $filename): bool
    {
        $filename = basename($filename);
        $filePath = $this->tempDir . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    private function validateUrl(string $url): void
    {
        $url = trim($url);

        if (empty($url)) {
            throw new Exception('URL is required.');
        }

        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            throw new Exception('Please enter a valid website URL.');
        }

        if (!in_array($parsed['scheme'], ['http', 'https'])) {
            throw new Exception('Only HTTP and HTTPS URLs are allowed.');
        }

        $host = strtolower($parsed['host']);

        foreach ($this->blockedHosts as $blocked) {
            if ($host === $blocked || $host === $blocked . ':80' || $host === $blocked . ':443') {
                throw new Exception('Internal/private URLs are not allowed.');
            }
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                throw new Exception('Private IP addresses are not allowed.');
            }
        }
    }

    private function validateDimensions(int $width, int $height): void
    {
        if ($width < 320 || $width > 3840) {
            throw new Exception('Width must be between 320 and 3840 pixels.');
        }

        if ($height < 320 || $height > 21600) {
            throw new Exception('Height must be between 320 and 21600 pixels.');
        }
    }

    private function findChromiumPath(): string
    {
        $paths = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new Exception('Chromium browser not found. Please install Google Chrome or run: npx playwright install chromium');
    }

    private function buildNodeScript(string $url, int $width, int $height, string $screenshotType, string $format, string $filePath): string
    {
        $fullPage = $screenshotType === 'full' ? 'true' : 'false';

        $mimeType = match ($format) {
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        $filePath = str_replace('\\', '/', $filePath);

        return <<<NODESCRIPT
const { chromium } = require('playwright');

(async () => {
    let browser;
    try {
        browser = await chromium.launch({ headless: true });
        const context = await browser.newContext({
            viewport: { width: {$width}, height: {$height} }
        });
        const page = await context.newPage();

        await page.goto({$this->quoteJs($url)}, {
            waitUntil: 'networkidle',
            timeout: 30000
        });

        await page.screenshot({
            path: {$this->quoteJs($filePath)},
            fullPage: {$fullPage},
            type: '{$format}',
            {$this->qualityOption($format)}
        });

        await browser.close();
        browser = null;
    } catch (error) {
        if (browser) await browser.close();
        process.stderr.write(error.message);
        process.exit(1);
    }
})();
NODESCRIPT;
    }

    private function quoteJs(string $value): string
    {
        $value = str_replace('\\', '/', $value);
        $value = str_replace("'", "\\'", $value);
        return "'{$value}'";
    }

    private function qualityOption(string $format): string
    {
        if ($format === 'jpeg') {
            return 'quality: 90,';
        }
        return '';
    }
}
