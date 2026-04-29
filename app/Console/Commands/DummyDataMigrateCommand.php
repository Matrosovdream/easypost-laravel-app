<?php

namespace App\Console\Commands;

use App\Services\DummyData\DummyDataMigrationService;
use Illuminate\Console\Command;
use Throwable;

class DummyDataMigrateCommand extends Command
{
    protected $signature = 'dummy:migrate';

    protected $description = 'Seed the database with EasyPost dummy data and connect it to the demo team and seeded users.';

    public function handle(DummyDataMigrationService $service): int
    {
        $this->info('Migrating EasyPost dummy data...');

        try {
            $summary = $service->run();
        } catch (Throwable $e) {
            $this->error('Dummy data migration failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $rows = [];
        foreach ($summary as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subVal) {
                    $rows[] = ["{$key}.{$subKey}", $subVal];
                }
            } else {
                $rows[] = [$key, $value];
            }
        }

        $this->table(['Entity', 'Imported'], $rows);
        $this->info('Done.');

        return self::SUCCESS;
    }
}
