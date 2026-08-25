<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateScreenshotRequest;
use App\Services\ScreenshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;

class ScreenshotController extends Controller
{
    public function __construct(
        private ScreenshotService $screenshotService
    ) {}

    public function generate(GenerateScreenshotRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->screenshotService->generate(
                url: $validated['url'],
                width: $validated['width'],
                height: $validated['height'],
                screenshotType: $validated['screenshot_type'],
                format: $validated['format'],
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            $message = match (true) {
                str_contains($e->getMessage(), 'took too long') =>
                    'The website took too long to respond. Please try again.',
                str_contains($e->getMessage(), 'not allowed') ||
                str_contains($e->getMessage(), 'valid website URL') =>
                    $e->getMessage(),
                str_contains($e->getMessage(), 'not found') =>
                    'Unable to access this website. Please check the URL and try again.',
                default =>
                    'Screenshot generation failed. Please try again.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }
    }

    public function download(string $filename): Response|JsonResponse
    {
        $filePath = $this->screenshotService->getFilePath($filename);

        if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'Screenshot not found or has expired.',
            ], 404);
        }

        $mimeType = match (pathinfo($filename, PATHINFO_EXTENSION)) {
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="screenshot-' . $filename . '"',
        ]);
    }
}
