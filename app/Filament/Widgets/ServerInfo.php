<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ServerInfo extends Widget
{
    // v4: properti non-static
    protected ?string $heading = 'Server Info';
    protected string $view = 'filament.widgets.server-info';

    protected function getViewData(): array
    {
        [$dbVersion, $dbDriver] = $this->getDatabaseInfo();

        $memoryLimit = ini_get('memory_limit') ?: 'N/A';
        $postMax = ini_get('post_max_size') ?: 'N/A';
        $uploadMax = ini_get('upload_max_filesize') ?: 'N/A';

        $items = [
            ['label' => 'PHP', 'value' => php_sapi_name() . ' - V' . PHP_VERSION, 'meta' => 'PHP Server: '],
            ['label' => 'Laravel', 'value' => (config('app.debug') ? 'debug:ON' : 'debug:OFF') . ' • V' . app()->version(), 'meta' => 'Laravel: ' . config('app.env')],
            ['label' => 'Database', 'value' => $dbDriver . ' - V' . $dbVersion, 'meta' => "Database: "],
            ['label' => 'Memory', 'value' => 'Limit: ' . $memoryLimit . " • Post: {$postMax} • Upload: {$uploadMax}", 'meta' => "PHP Config"],
            ['label' => 'Timezone', 'value' => now()->toDateTimeString() . ' ' . date_default_timezone_get(), 'meta' => "Timezone: "],
        ];

        return compact('items');
    }

    /** Helpers */
    private function getDatabaseInfo(): array
    {
        try {
            $row = DB::selectOne('SELECT VERSION() AS v');
            $version = $row?->v ?? DB::getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable $e) {
            $version = 'N/A';
        }

        try {
            $driver = DB::getDriverName(); // mysql, pgsql, sqlite, sqlsrv
        } catch (\Throwable $e) {
            $driver = 'N/A';
        }

        if (is_string($version) && stripos($version, 'mariadb') !== false) {
            $driver .= ' (MariaDB)';
        }

        return [(string) $version, (string) $driver];
    }

    private function formatBytes($bytes): string
    {
        if (!is_numeric($bytes) || $bytes <= 0)
            return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = (int) floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);
        return number_format($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }
}
