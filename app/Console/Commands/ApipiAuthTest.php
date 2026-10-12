<?php

namespace App\Console\Commands;

use App\Services\ExternalApi\ApipiAuthService;
use Illuminate\Console\Command;

class ApipiAuthTest extends Command
{
    protected $signature = 'apipi:auth-test
        {--probe= : Comma-separated endpoint paths to probe (overrides the default list)}
        {--fresh : Force a fresh login instead of reusing the stored session}';

    protected $description = 'Login to the apipi pickup API and probe the available endpoints';

    /**
     * Default candidate endpoints derived from the login permissions.
     * Used only to DISCOVER the real paths (status codes reveal which exist).
     */
    const DEFAULT_PROBES = [
        '/api-master-supplier',
        '/api-master-kategori-supplier',
        '/api-jenis-supplier',
        '/api-surat-jalan',
        '/api-surat-jalan/all',
        '/api-work-order',
        '/api-master-gudang',
        '/api-master-lokasi-provinsi',
        '/api-master-lokasi-kota',
        '/api-uji-quality',
        '/api-movement',
        '/api-inventory',
        '/api-mutasi',
    ];

    public function handle()
    {
        $service = app(ApipiAuthService::class);

        try {
            if ($this->option('fresh')) {
                $this->info('Forcing fresh login...');
                $body = $service->login();
            } else {
                $service->ensureAuthenticated();
                $body = $service->sessionInfo()['user'] ?? null
                    ? ['data' => $service->sessionInfo()['user']]
                    : $service->login();
            }
        } catch (\Throwable $e) {
            $this->error('Authentication failed: ' . $e->getMessage());
            return 1;
        }

        $data = $body['data'] ?? [];

        $this->line("");
        $this->line('<info>=== Session ===</info>');
        $this->line('  User     : ' . ($data['name'] ?? '-') . ' <' . ($data['email'] ?? '-') . '>');
        $this->line('  User ID  : ' . ($data['user_id'] ?? '-'));
        $this->line('  Roles    : ' . implode(', ', $data['roles'] ?? []));
        $this->line('  Perms    : ' . count($data['permissions'] ?? []) . ' permission(s)');
        $this->line('  Cookies  : ' . implode(', ', $service->cookieNames()));
        $this->line('  Jar      : ' . $service->cookieFilePath());
        $this->line("");

        $probes = $this->option('probe')
            ? array_filter(array_map('trim', explode(',', $this->option('probe'))))
            : self::DEFAULT_PROBES;

        $this->line('<info>=== Endpoint probe ===</info>');
        $rows = [];
        foreach ($probes as $path) {
            $res    = $service->get($path);
            $status = $res['status'];
            $snippet = '';
            if (is_array($res['body'])) {
                $coded = $res['body']['message'] ?? ($res['body']['status'] ?? '');
                $count = isset($res['body']['data']) && is_array($res['body']['data']) ? count($res['body']['data']) : null;
                $snippet = trim((string) $coded) . ($count !== null ? " ({$count} rows)" : '');
            } else {
                $snippet = substr(preg_replace('/\s+/', ' ', strip_tags($res['raw'])), 0, 60);
            }

            $flag = $status >= 200 && $status < 300 ? '<info>OK</info>' : ($status === 401 || $status === 403 ? '<comment>AUTH</comment>' : '<error>' . $status . '</error>');
            $this->line(sprintf('  [%-10s] %-32s %s', strip_tags($flag), $path, $snippet));
            $rows[] = [$status, $path, $snippet];
        }

        $this->line("");
        $this->line('Tip: pass --probe=/path1,/path2 to test specific endpoints.');

        return 0;
    }
}
