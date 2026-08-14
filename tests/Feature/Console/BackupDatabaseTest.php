<?php

namespace Tests\Feature\Console;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * O pg_dump real é exercitado manualmente (ver docs/DEPLOY.md) contra uma
 * base de dados real — não faz sentido depender do binário estar instalado
 * para a suite correr em qualquer máquina/CI. Aqui usa-se Process::fake()
 * para verificar a orquestração do comando (argumentos passados, escrita no
 * disco configurado, purga de retenção), simulando o efeito de escrita do
 * pg_dump no ficheiro --file= apontado.
 */
class BackupDatabaseTest extends TestCase
{
    private function fakePgDumpWriting(string $contents = '-- dump de teste --'): void
    {
        Process::fake(function (PendingProcess $process) use ($contents) {
            $fileArg = collect($process->command)->first(fn ($arg) => str_starts_with($arg, '--file='));
            file_put_contents(substr($fileArg, strlen('--file=')), $contents);

            return Process::result();
        });
    }

    public function test_creates_a_dump_file_on_the_configured_disk(): void
    {
        Storage::fake('local');
        $this->fakePgDumpWriting();

        $this->artisan('app:backup-database')->assertSuccessful();

        $files = Storage::disk('local')->files('backups');
        $this->assertCount(1, $files);
        $this->assertStringContainsString('agrolink_mz_test', $files[0]);
        $this->assertStringContainsString('-- dump de teste --', Storage::disk('local')->get($files[0]));
    }

    public function test_returns_failure_and_writes_nothing_when_pg_dump_fails(): void
    {
        Storage::fake('local');
        Process::fake(fn () => Process::result(errorOutput: 'ligação recusada', exitCode: 1));

        $this->artisan('app:backup-database')->assertFailed();

        $this->assertEmpty(Storage::disk('local')->files('backups'));
    }

    public function test_prunes_backups_older_than_the_retention_window(): void
    {
        Storage::fake('local');
        config(['backup.keep_days' => 7]);

        Storage::disk('local')->put('backups/old.sql', 'dump antigo');
        touch(Storage::disk('local')->path('backups/old.sql'), now()->subDays(10)->timestamp);

        $this->fakePgDumpWriting('-- novo --');

        $this->artisan('app:backup-database')->assertSuccessful();

        $files = Storage::disk('local')->files('backups');
        $this->assertCount(1, $files, 'O dump antigo devia ter sido purgado, ficando só o novo.');
        $this->assertStringNotContainsString('old.sql', $files[0]);
    }
}