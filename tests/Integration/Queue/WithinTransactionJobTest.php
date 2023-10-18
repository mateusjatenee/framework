<?php

namespace Illuminate\Tests\Integration\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\RunsWithinTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use RuntimeException;

class WithinTransactionJobTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.connections.custom', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::connection('custom')->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });
    }

    public function testItRollbacksATransactionIfAJobFails()
    {
        try {
            WithinTransactionFailingJob::dispatch();
        } catch (WithinTransactionJobFailedException) {

        }

        $this->assertEquals(0, DB::connection('custom')->table('users')->count());
        $this->assertTrue(WithinTransactionFailingJob::$ran);
    }
}

class WithinTransactionFailingJob implements RunsWithinTransaction
{
    use InteractsWithQueue, Queueable, Dispatchable;

    public static $ran = false;

    public $databaseConnection = 'custom';

    public function handle()
    {
        static::$ran = true;

        DB::connection('custom')->table('users')->insert([
            'email' => 'mateus@foo.com',
        ]);

        throw new WithinTransactionJobFailedException;
    }
}

class WithinTransactionJobFailedException extends RuntimeException
{
}
