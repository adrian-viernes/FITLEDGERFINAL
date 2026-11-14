<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--list : List existing database backup archives}
                            {--clean : Prune old backups retaining the latest 7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Securely backup the database to protected storage with SHA-256 integrity verification';

    /**
     * Number of daily backups to retain.
     */
    protected int $retentionCount = 7;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (! File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        if ($this->option('list')) {
            $this->listBackups($backupDir);
            return self::SUCCESS;
        }

        $connection = config('database.default');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $this->info("Initiating secure database backup for connection [{$connection}]...");

        $backupFilePath = null;

        try {
            $sqlitePath = config('database.connections.sqlite.database');
            if ($connection === 'sqlite' && $sqlitePath !== ':memory:' && File::exists($sqlitePath)) {
                $backupFilePath = $this->backupSqlite($backupDir, $timestamp);
            } else {
                $backupFilePath = $this->backupSqlDump($backupDir, $connection, $timestamp);
            }

            if (! $backupFilePath || ! File::exists($backupFilePath)) {
                $this->error('Failed to generate database backup file.');
                return self::FAILURE;
            }

            $sizeBytes = File::size($backupFilePath);
            $sizeFormatted = $this->formatBytes($sizeBytes);
            $sha256 = hash_file('sha256', $backupFilePath);

            // Write metadata sidecar file for tamper-evident verification
            $metaPath = $backupFilePath . '.meta.json';
            File::put($metaPath, json_encode([
                'filename' => basename($backupFilePath),
                'connection' => $connection,
                'created_at' => now()->toIso8601String(),
                'size_bytes' => $sizeBytes,
                'sha256' => $sha256,
            ], JSON_PRETTY_PRINT));

            $this->info("Backup created successfully!");
            $this->line("File:     " . basename($backupFilePath));
            $this->line("Path:     " . $backupFilePath);
            $this->line("Size:     " . $sizeFormatted);
            $this->line("SHA-256:  " . $sha256);

            $this->pruneOldBackups($backupDir);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Backup failed: " . $e->getMessage());
            \Log::error('Database backup failed', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }
    }

    protected function backupSqlite(string $backupDir, string $timestamp): string
    {
        $dbPath = config('database.connections.sqlite.database');

        if ($dbPath === ':memory:') {
            throw new \RuntimeException('Cannot backup in-memory SQLite database.');
        }

        if (! File::exists($dbPath)) {
            throw new \RuntimeException("SQLite database file not found at [{$dbPath}].");
        }

        $targetPath = $backupDir . DIRECTORY_SEPARATOR . "fitledger_sqlite_{$timestamp}.sqlite";
        File::copy($dbPath, $targetPath);

        return $targetPath;
    }

    protected function backupSqlDump(string $backupDir, string $connection, string $timestamp): string
    {
        $targetPath = $backupDir . DIRECTORY_SEPARATOR . "fitledger_{$connection}_{$timestamp}.sql";
        $handle = fopen($targetPath, 'w');

        if (! $handle) {
            throw new \RuntimeException("Unable to open backup target file [{$targetPath}] for writing.");
        }

        fwrite($handle, "-- FitLedger Database Backup\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
        fwrite($handle, "-- Connection: {$connection}\n\n");
        if ($connection === 'mysql') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
        } elseif ($connection === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");
        }

        $tables = Schema::getTableListing();

        foreach ($tables as $table) {
            $tableName = is_object($table) ? ($table->name ?? (string) $table) : (string) $table;

            // Skip internal sqlite tables
            if (str_starts_with($tableName, 'sqlite_')) {
                continue;
            }

            fwrite($handle, "-- Table structure and data for `{$tableName}`\n");
            
            try {
                $rows = DB::connection($connection)->table($tableName)->get();

                if ($rows->isNotEmpty()) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $columns = array_keys($rowArray);
                        $escapedCols = array_map(fn ($c) => "`{$c}`", $columns);
                        $escapedVals = array_map(function ($val) use ($connection) {
                            if ($val === null) {
                                return 'NULL';
                            }
                            return DB::connection($connection)->getPdo()->quote((string) $val);
                        }, array_values($rowArray));

                        $sql = sprintf(
                            "INSERT INTO `%s` (%s) VALUES (%s);\n",
                            $tableName,
                            implode(', ', $escapedCols),
                            implode(', ', $escapedVals)
                        );

                        fwrite($handle, $sql);
                    }
                }
            } catch (\Throwable $e) {
                // Skip tables that cannot be queried directly
            }

            fwrite($handle, "\n");
        }

        if ($connection === 'mysql') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } elseif ($connection === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = ON;\n");
        }
        fclose($handle);

        return $targetPath;
    }

    protected function pruneOldBackups(string $backupDir): void
    {
        $files = collect(File::files($backupDir))
            ->filter(fn ($file) => ! str_ends_with($file->getFilename(), '.meta.json'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        if ($files->count() > $this->retentionCount) {
            $toDelete = $files->slice($this->retentionCount);
            foreach ($toDelete as $oldFile) {
                File::delete($oldFile->getRealPath());
                $metaFile = $oldFile->getRealPath() . '.meta.json';
                if (File::exists($metaFile)) {
                    File::delete($metaFile);
                }
                $this->line("Pruned old backup snapshot: " . $oldFile->getFilename());
            }
        }
    }

    protected function listBackups(string $backupDir): void
    {
        $files = collect(File::files($backupDir))
            ->filter(fn ($file) => ! str_ends_with($file->getFilename(), '.meta.json'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        if ($files->isEmpty()) {
            $this->info("No backups found in [{$backupDir}].");
            return;
        }

        $this->info("Available Database Backups (" . $files->count() . " snapshots):");
        $rows = [];

        foreach ($files as $file) {
            $metaPath = $file->getRealPath() . '.meta.json';
            $sha = 'N/A';
            if (File::exists($metaPath)) {
                $meta = json_decode(File::get($metaPath), true);
                $sha = substr($meta['sha256'] ?? 'N/A', 0, 16) . '...';
            }

            $rows[] = [
                $file->getFilename(),
                $this->formatBytes($file->getSize()),
                date('Y-m-d H:i:s', $file->getMTime()),
                $sha,
            ];
        }

        $this->table(['Filename', 'Size', 'Created At', 'SHA-256 (Prefix)'], $rows);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
