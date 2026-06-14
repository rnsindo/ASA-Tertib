<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Seeder Sync Mode
    |--------------------------------------------------------------------------
    |
    | add_only: hanya menambahkan data default yang belum ada.
    | sync: update data default dari kode seeder ke database.
    |
    */
    'sync_mode' => env('SEED_SYNC_MODE', 'add_only'),
];
