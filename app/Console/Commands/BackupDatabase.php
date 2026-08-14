<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Secção 22 — "Backups automáticos e testados (restauro real)". Este comando
 * trata da parte automática (dump diário + purga do que passou da retenção);
 * o restauro em si é deliberadamente manual (secção "Recuperação" do
 * docs/DEPLOY.md) — automatizar o restauro tornaria fácil apagar dados de
 * produção por engano a partir de um comando agendado.
 */
#[Signature('app:backup-database')]
#[Description('Gera um dump da base de dados PostgreSQL e guarda-o no disco de backups configurado, purgando os mais antigos.')]
class BackupDatabase extends Command
{
    public function handle(): int
    {
        $connection = config('database.connections.'.config('database.default'));
        $tempPath = tempnam(sys_get_temp_dir(), 'agrolink_backup_');

        $result = Process::env(['PGPASSWORD' => $connection['password'] ?? ''])
            ->run([
                config('backup.pg_dump_path'),
                '--host='.$connection['host'],
                '--port='.$connection['port'],
                '--username='.$connection['username'],
                '--format=plain',
                '--no-owner',
                '--no-privileges',
                '--file='.$tempPath,
                $connection['database'],
            ]);

        if ($result->failed()) {
            @unlink($tempPath);
            $this->error('Falha ao gerar o backup: '.$result->errorOutput());

            return self::FAILURE;
        }

        $filename = sprintf('%s_%s.sql', $connection['database'], now()->format('Y-m-d_His'));
        $disk = Storage::disk(config('backup.disk'));
        $disk->put(config('backup.directory').'/'.$filename, file_get_contents($tempPath));
        @unlink($tempPath);

        $this->info("Backup guardado: {$filename}");
        $this->pruneOldBackups($disk);

        return self::SUCCESS;
    }

    private function pruneOldBackups(FilesystemAdapter $disk): void
    {
        $cutoff = now()->subDays(config('backup.keep_days'))->timestamp;

        foreach ($disk->files(config('backup.directory')) as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);
            }
        }
    }
}