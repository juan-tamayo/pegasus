<?php

namespace App\Services\Mail;

use Illuminate\Validation\ValidationException;
use Throwable;
use Webklex\PHPIMAP\ClientManager;

class ImapConnectionTester
{
    public function test(array $data): void
    {
        $host = $data['host'];
        if (! in_array($host, config('pegasus.imap_allowed_hosts'), true)) {
            throw ValidationException::withMessages(['host' => 'This mail host must be allowed in server configuration.']);
        }
        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        $ips = [];
        foreach ($records ?: [] as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($ip) {
                if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    // Fail closed if any DNS response points at a private or reserved target.
                    throw ValidationException::withMessages(['host' => 'The server must resolve exclusively to public IPv4 mail addresses.']);
                }
                if (! str_contains($ip, ':')) {
                    $ips[] = $ip;
                }
            }
        }
        if (! $ips) {
            throw ValidationException::withMessages(['host' => 'Could not resolve this mail host to a supported public address.']);
        }
        // Pin the verified address so DNS cannot change between validation and connection.
        $client = (new ClientManager(['options' => ['debug' => false]]))->make([
            'host' => $ips[0], 'port' => (int) $data['port'], 'protocol' => 'imap',
            'encryption' => $data['encryption'], 'validate_cert' => true,
            'ssl_options' => ['peer_name' => $host, 'SNI_enabled' => true, 'allow_self_signed' => false],
            'username' => $data['username'], 'password' => $data['password'], 'timeout' => 10,
        ]);
        try {
            $client->connect();
            $client->examineFolder('INBOX');
        } catch (Throwable) {
            throw ValidationException::withMessages(['password' => 'Could not securely sign in and read INBOX. Check your credentials, provider settings, and TLS connection.']);
        } finally {
            try {
                $client->disconnect();
            } catch (Throwable) { /* Never surface provider payloads. */
            }
        }
    }
}
