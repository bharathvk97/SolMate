<?php
// app/Console/Commands/ExpireOwnerAccounts.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ExpireOwnerAccounts extends Command
{
    protected $signature   = 'owners:expire';
    protected $description = 'Deactivate hostel/mess owner accounts whose subscription has expired';

    public function handle(): int
    {
        $count = User::whereIn('role', ['hostel_owner', 'mess_owner'])
            ->where('subscription_status', 'active')
            ->where('subscription_expires_at', '<', now())
            ->update([
                'subscription_status' => 'expired',
                'status'              => 'inactive',
            ]);

        $this->info("✓ Deactivated {$count} expired owner account(s).");

        return self::SUCCESS;
    }
}
