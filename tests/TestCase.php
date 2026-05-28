<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Sementeia as roles da plataforma após cada refresh de banco.
     *
     * O Spatie Permission exige que os registros de role existam antes de
     * chamar assignRole() em qualquer teste. Fazemos isso aqui ao invés de
     * no Pest.php para centralizar e evitar repetição em cada arquivo de teste.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }
}
