<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:backup', function () {
    $connection = config('database.default');

    if (! in_array($connection, ['mysql', 'mariadb'], true)) {
        $this->warn('Database backup is only available for MySQL/MariaDB connections.');

        return self::FAILURE;
    }

    $config = config("database.connections.{$connection}");
    $database = $config['database'] ?? null;
    $host = $config['host'] ?? '127.0.0.1';
    $port = $config['port'] ?? '3306';
    $username = $config['username'] ?? 'root';
    $password = $config['password'] ?? '';

    if (! $database) {
        $this->error('No database name is configured for the active database connection.');

        return self::FAILURE;
    }

    $mysqldumpCheck = trim((string) shell_exec('command -v mysqldump 2>/dev/null || where mysqldump 2>/dev/null'));
    if ($mysqldumpCheck === '') {
        $this->error('mysqldump is not installed or not available on PATH.');

        return self::FAILURE;
    }

    $dir = storage_path('app/backups');
    if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
        $this->error('Unable to create the backup directory at ' . $dir);

        return self::FAILURE;
    }

    $filename = now()->format('Ymd_His') . '_' . $connection . '.sql';
    $backupPath = $dir . DIRECTORY_SEPARATOR . $filename;

    $command = sprintf(
        'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
        escapeshellarg($host),
        escapeshellarg((string) $port),
        escapeshellarg($username),
        escapeshellarg($password),
        escapeshellarg($database),
        escapeshellarg($backupPath),
    );

    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        $this->error('Database backup failed: ' . implode(PHP_EOL, $output));

        return self::FAILURE;
    }

    $this->info('Database backup created at ' . $backupPath);

    return self::SUCCESS;
})->purpose('Create a MySQL backup using mysqldump');

Artisan::command('patients:repair-encryption', function () {
    $this->info('Scanning patients for legacy plaintext sensitive fields...');

    $updated = 0;
    $alreadyEncrypted = 0;
    $skipped = 0;

    $patients = DB::table('patients')->select('id', 'allergies', 'insurance_number')->get();

    foreach ($patients as $patientRow) {
        foreach (['allergies', 'insurance_number'] as $field) {
            $rawValue = $patientRow->{$field};

            if ($rawValue === null || $rawValue === '') {
                $skipped++;
                continue;
            }

            try {
                Crypt::decrypt((string) $rawValue);
                $alreadyEncrypted++;
                continue;
            } catch (\Throwable $e) {
                // Raw value is legacy plaintext or otherwise invalid ciphertext and needs to be encrypted via the model cast.
            }

            $patient = new \App\Models\Patient();
            $patient->exists = true;
            $patient->id = $patientRow->id;
            $patient->{$field} = $rawValue;
            $patient->save();
            $updated++;
        }
    }

    $this->info("Patients scanned: {$patients->count()}");
    $this->info("Already encrypted: {$alreadyEncrypted}");
    $this->info("Legacy plaintext repaired: {$updated}");
    $this->info("Null/empty values skipped: {$skipped}");

    return self::SUCCESS;
})->purpose('Repair legacy plaintext patient allergies and insurance_number values by re-encrypting them through the model.');

Schedule::command('db:backup')->dailyAt('02:00');
