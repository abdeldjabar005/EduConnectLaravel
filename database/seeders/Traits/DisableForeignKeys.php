<?php


namespace Database\Seeders\Traits;


use Illuminate\Support\Facades\DB;

trait DisableForeignKeys
{
    protected function disableForeignKeys()

    {
        DB::statement('SET session_replication_role = replica');
    }

    protected function enableForeignKeys()
    {
        DB::statement('SET session_replication_role = DEFAULT');
    }
}
