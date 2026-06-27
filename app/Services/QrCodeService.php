<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    /**
     * Generate QR code SVG untuk ditampilkan di web (QR Cabang).
     */
    public function generateSvg(string $url, int $size = 200): string
    {
        return QrCode::size($size)->generate($url);
    }

    /**
     * Generate QR code SVG untuk tiket pelanggan.
     */
    public function generateCustomerQrSvg(string $statusUrl, int $size = 200): string
    {
        return QrCode::size($size)->generate($statusUrl);
    }

    /**
     * Generate QR code PNG dan simpan ke storage.
     * Menggunakan SVG sebagai basis lalu dikonversi ke PNG via GD.
     * Fallback: simpan SVG dengan ekstensi .png jika GD tidak support.
     */
    public function generateAndStore(string $branchCode, string $type = 'branch'): string
    {
        $url      = $this->getBranchUrl($branchCode);
        $filename = "qrcodes/{$branchCode}_{$type}.png";

        // Coba generate PNG via imagick atau GD
        try {
            // Method 1: coba format PNG langsung (butuh imagick/gd)
            $qrImage = QrCode::format('png')
                ->size(400)
                ->margin(2)
                ->generate($url);

            Storage::disk('public')->put($filename, $qrImage);
        } catch (\Throwable $e) {
            // Fallback: generate SVG, lalu wrap dalam PNG via GD jika tersedia
            $svgContent = QrCode::size(400)->margin(2)->generate($url);

            if (extension_loaded('gd') && function_exists('imagecreatefrompng')) {
                // Simpan SVG sebagai file temp, convert ke PNG via GD
                $pngData = $this->svgToPngViaGd($svgContent, 400);
                if ($pngData) {
                    Storage::disk('public')->put($filename, $pngData);
                } else {
                    // Store SVG with .png extension as last resort
                    Storage::disk('public')->put($filename, $svgContent);
                }
            } else {
                // Store SVG with .png extension
                Storage::disk('public')->put($filename, $svgContent);
            }
        }

        return $filename;
    }

    /**
     * Generate QR code PNG untuk tiket pelanggan (customer queue QR).
     */
    public function generateCustomerQrPng(string $url, int $size = 400): ?string
    {
        try {
            return QrCode::format('png')
                ->size($size)
                ->margin(2)
                ->generate($url);
        } catch (\Throwable $e) {
            // Fallback: return null, caller akan gunakan SVG
            return null;
        }
    }

    /**
     * Generate QR code SVG file dan simpan ke storage (untuk download).
     */
    public function generateAndStoreSvg(string $branchCode): string
    {
        $url      = $this->getBranchUrl($branchCode);
        $filename = "qrcodes/{$branchCode}_branch.svg";

        $svg = QrCode::size(400)->margin(2)->generate($url);
        Storage::disk('public')->put($filename, $svg);

        return $filename;
    }

    /**
     * Get URL untuk halaman cabang (digunakan pada QR cabang).
     * SELALU menggunakan APP_URL (ngrok/production), bukan request host.
     * Tambah ngrok-skip-browser-warning agar HP langsung masuk tanpa interstitial.
     */
    public function getBranchUrl(string $branchCode): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        $request = request();

        // Fallback detection: if current request is already accessed via ngrok, prefer it over stale config
        $currentHost = $request->getHost();
        $currentScheme = $request->getScheme();
        
        if (str_contains($currentHost, 'ngrok')) {
            $appUrl = $currentScheme . '://' . $currentHost;
        }

        URL::forceRootUrl($appUrl);
        // Requirement 4: Ensure ALL QR URLs always use route(..., absolute: true)
        $url = route('branch.detail', $branchCode, true);
        URL::forceRootUrl(null);

        // Bypass ngrok interstitial page saat scan dari HP
        if (str_contains($appUrl, 'ngrok')) {
            $url .= '?ngrok-skip-browser-warning=true';
        }

        // Requirement 5: Add debug logging
        Log::info("Generated QR Branch URL for {$branchCode}: {$url} | APP_URL: " . config('app.url') . " | Current Host: {$currentHost}");

        return $url;
    }

    /**
     * Cek konfigurasi APP_URL untuk warning integrasi ngrok.
     */
    public function validateAppUrl(): array
    {
        $appUrl = rtrim(config('app.url'), '/');
        $parsedAppUrl = parse_url($appUrl);
        $appHost = $parsedAppUrl['host'] ?? '';
        $appPort = $parsedAppUrl['port'] ?? ($parsedAppUrl['scheme'] === 'https' ? 443 : 80);

        $request = request();
        $currentHost = $request->getHost();
        $currentPort = $request->getPort();

        $warnings = [];

        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $warnings[] = 'APP_URL menggunakan localhost atau 127.0.0.1.';
        }

        if ($appHost !== $currentHost) {
            $warnings[] = "Host APP_URL ({$appHost}) berbeda dengan host yang Anda akses saat ini ({$currentHost}).";
        }

        if ($appPort != $currentPort) {
            $warnings[] = "Port APP_URL (" . ($parsedAppUrl['port'] ?? 'default') . ") berbeda dengan port yang berjalan saat ini ({$currentPort}).";
        }

        return [
            'has_warning'  => count($warnings) > 0,
            'warnings'     => $warnings,
            'app_url'      => $appUrl,
            'current_host' => $currentHost,
            'current_port' => $currentPort,
        ];
    }

    /**
     * Get URL untuk halaman status antrian pelanggan.
     * SELALU menggunakan APP_URL (ngrok/production).
     */
    public function getCustomerStatusUrl(string $branchCode, string $queueQr): string
    {
        $appUrl = rtrim(config('app.url'), '/');
        URL::forceRootUrl($appUrl);
        $url = route('queue.status', ['branch_code' => $branchCode, 'token' => $queueQr]);
        URL::forceRootUrl(null);

        if (str_contains($appUrl, 'ngrok')) {
            $url .= '?ngrok-skip-browser-warning=true';
        }

        return $url;
    }

    /**
     * Get public storage path untuk QR PNG.
     */
    public function getStoragePath(string $branchCode, string $type): string
    {
        return storage_path("app/public/qrcodes/{$branchCode}_{$type}.png");
    }

    /**
     * Get public storage path untuk SVG.
     */
    public function getSvgStoragePath(string $branchCode): string
    {
        return storage_path("app/public/qrcodes/{$branchCode}_branch.svg");
    }

    /**
     * Coba konversi SVG string ke PNG data menggunakan GD.
     * Hanya bekerja jika GD punya dukungan SVG (jarang).
     * Return null jika tidak bisa.
     */
    private function svgToPngViaGd(string $svgContent, int $size): ?string
    {
        // GD tidak bisa load SVG secara native
        // Buat PNG kosong dengan teks QR tidak tersedia
        // Ini adalah fallback terakhir
        return null;
    }
}
