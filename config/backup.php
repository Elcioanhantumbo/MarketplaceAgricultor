<?php

return [
    // Secção 22 — backups automáticos: onde os dumps ficam guardados.
    'disk' => env('BACKUP_DISK', 'local'),
    'directory' => env('BACKUP_DIRECTORY', 'backups'),

    // Quantos dias de dumps manter antes de apagar os mais antigos.
    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),

    // Caminho para o executável pg_dump — por omissão espera-o no PATH
    // (é o caso normal em Linux com postgresql-client instalado); em
    // ambientes onde não está no PATH, define BACKUP_PG_DUMP_PATH.
    'pg_dump_path' => env('BACKUP_PG_DUMP_PATH', 'pg_dump'),
];