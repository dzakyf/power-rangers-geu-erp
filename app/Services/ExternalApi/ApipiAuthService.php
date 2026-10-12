<?php

namespace App\Services\ExternalApi;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Support\Facades\Log;

/**
 * Authentication/session handler for the external pickup API
 * (apipi.greenenergiutama.co.id).
 *
 * The API authenticates via cookies: POST /api-auth/login sets
 *   - auth_token    (JWT access token, max-age 1800s / 30 min)
 *   - refresh_token (JWT refresh token, max-age 2592000s / 30 days)
 * There is no refresh endpoint, so on expiry we simply log in again.
 *
 * The cookie jar is persisted server-side (FileCookieJar) so the login
 * survives across PHP processes/requests.
 */
class ApipiAuthService
{
    /** @var string */
    protected $baseUrl;
    /** @var string|null */
    protected $email;
    /** @var string|null */
    protected $password;
    /** @var int */
    protected $timeout;
    /** @var bool */
    protected $verify;
    /** @var int */
    protected $sessionTtl;

    /** @var string */
    protected $cookieFile;
    /** @var string */
    protected $sessionFile;

    /** @var Client|null */
    protected $client;
    /** @var FileCookieJar|null */
    protected $jar;

    public function __construct()
    {
        $cfg = config('services.apipi', []);

        $this->baseUrl    = rtrim($cfg['base_url'] ?? '', '/');
        $this->email      = $cfg['email'] ?? null;
        $this->password   = $cfg['password'] ?? null;
        $this->timeout    = (int) ($cfg['timeout'] ?? 30);
        $this->verify     = (bool) ($cfg['verify'] ?? true);
        $this->sessionTtl = (int) ($cfg['session_ttl'] ?? 1500);

        $dir = storage_path('app/apipi');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $this->cookieFile  = $dir . '/cookies.json';
        $this->sessionFile = $dir . '/session.json';
    }

    /**
     * Authenticated Guzzle client (shared cookie jar).
     */
    public function client(): Client
    {
        if ($this->client === null) {
            $this->jar = new FileCookieJar($this->cookieFile, true);

            $this->client = new Client([
                'base_uri'    => $this->baseUrl . '/',
                'timeout'     => $this->timeout,
                'verify'      => $this->verify,
                'cookies'     => $this->jar,
                'http_errors' => false,
                'headers'     => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);
        }

        return $this->client;
    }

    /**
     * Perform a fresh login and persist the resulting cookies.
     *
     * @return array decoded login response body
     */
    public function login(): array
    {
        if (!$this->email || !$this->password) {
            throw new \RuntimeException('Apipi API credentials are not configured (APIPI_EMAIL / APIPI_PASSWORD).');
        }

        $res  = $this->client()->post('api-auth/login', [
            'json' => [
                'email'    => $this->email,
                'password' => $this->password,
            ],
        ]);

        $body   = json_decode((string) $res->getBody(), true);
        $status = $res->getStatusCode();

        if ($status !== 200 || !is_array($body) || ($body['status'] ?? null) !== 'success') {
            $msg = is_array($body) ? ($body['message'] ?? 'unknown error') : 'unreadable response';
            throw new \RuntimeException("Apipi login failed (HTTP {$status}): {$msg}");
        }

        // persist cookie jar + session metadata
        $this->jar->save($this->cookieFile);
        $this->writeSession([
            'logged_in_at' => time(),
            'user'         => $body['data'] ?? [],
            'cookies'      => $this->cookieNames(),
        ]);

        Log::info('Apipi login OK', ['user' => $body['data']['email'] ?? null, 'cookies' => $this->cookieNames()]);

        return $body;
    }

    /**
     * Ensure a valid session exists (reuse persisted cookies within TTL,
     * otherwise perform a fresh login).
     */
    public function ensureAuthenticated(): void
    {
        if (!$this->hasFreshSession()) {
            $this->login();
        }
    }

    public function hasFreshSession(): bool
    {
        if (!is_file($this->cookieFile)) {
            return false;
        }

        $session = $this->readSession();
        if (!$session || empty($session['logged_in_at'])) {
            return false;
        }

        if ((time() - (int) $session['logged_in_at']) >= $this->sessionTtl) {
            return false;
        }

        return true;
    }

    /**
     * Authenticated GET with a single re-login retry on 401.
     *
     * @return array{status:int, body:mixed, raw:string, headers:array}
     */
    public function get(string $path, array $query = []): array
    {
        $this->ensureAuthenticated();

        $res = $this->client()->get(ltrim($path, '/'), ['query' => $query]);

        if ($res->getStatusCode() === 401) {
            // access token expired -> re-login once and retry
            $this->login();
            $res = $this->client()->get(ltrim($path, '/'), ['query' => $query]);
        }

        $raw = (string) $res->getBody();

        return [
            'status'  => $res->getStatusCode(),
            'body'    => json_decode($raw, true),
            'raw'     => $raw,
            'headers' => $res->getHeaders(),
        ];
    }

    public function sessionInfo(): ?array
    {
        return $this->readSession();
    }

    /** @return string[] cookie names currently held in the jar */
    public function cookieNames(): array
    {
        $this->client();

        $names = [];
        foreach ($this->jar->toArray() as $cookie) {
            if (!empty($cookie['Name'])) {
                $names[] = $cookie['Name'];
            }
        }

        return array_values(array_unique($names));
    }

    public function logout(): void
    {
        if (is_file($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
        if (is_file($this->sessionFile)) {
            @unlink($this->sessionFile);
        }
    }

    public function cookieFilePath(): string
    {
        return $this->cookieFile;
    }

    protected function readSession(): ?array
    {
        if (!is_file($this->sessionFile)) {
            return null;
        }
        $data = json_decode((string) @file_get_contents($this->sessionFile), true);

        return is_array($data) ? $data : null;
    }

    protected function writeSession(array $data): void
    {
        @file_put_contents($this->sessionFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
