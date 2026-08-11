<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PatchZkteco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:patch-zkteco';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Patch rats/zkteco to fix socket functions undefined errors';

    public function handle()
    {
        $path = base_path('vendor/rats/zkteco/src/Lib/ZKTeco.php');
        
        if (!file_exists($path)) {
            $this->warn('ZKTeco package not found at ' . $path);
            return;
        }

        $content = file_get_contents($path);

        // Check if already patched
        if (str_contains($content, 'use function socket_create;')) {
            $this->info('ZKTeco is already patched.');
            return;
        }

        // Add the use function statements after the last use statement
        $patch = <<<'PATCH'
use Rats\Zkteco\Lib\Helper\WorkCode;

// Patch: explicitly import PHP socket functions into this namespace
// to fix "Call to undefined function Rats\Zkteco\Lib\socket_*" errors
use function socket_create;
use function socket_set_option;
use function socket_connect;
use function socket_sendto;
use function socket_recvfrom;
use function socket_recv;
use function socket_send;
use function socket_read;
use function socket_write;
use function socket_close;
use function socket_last_error;
use function socket_strerror;
use function socket_clear_error;
PATCH;

        $content = str_replace('use Rats\Zkteco\Lib\Helper\WorkCode;', $patch, $content);
        file_put_contents($path, $content);

        $this->info('Successfully patched ZKTeco library!');
    }
}
