<?php

namespace MokoGithub\KerberosAuth\Console\Commands;

use Illuminate\Console\Command;
use MokoGithub\KerberosAuth\Models\KerberosAttempt;

class PurgeKerberosAttempts extends Command
{
    protected $signature = 'kerberos:purge-attempts {--days=30 : Number of days to retain}';

    protected $description = 'Purge Kerberos login attempts older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = KerberosAttempt::purgeOld($days)->delete();

        if ($deleted > 0) {
            $this->info("Purged {$deleted} Kerberos attempt(s) older than {$days} days.");
        } else {
            $this->info('No old Kerberos attempts to purge.');
        }

        return Command::SUCCESS;
    }
}
