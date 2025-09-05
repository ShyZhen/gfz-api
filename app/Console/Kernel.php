<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\EsInit::class,
        \App\Console\Commands\FmockInstall::class,
        \App\Console\Commands\RabbitMQ::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

//        $this->runInCommon($schedule);
//        $this->runInLocal($schedule);
//        $this->runInProdLocal($schedule);
//        $this->runInProduction($schedule);
    }

    /**
     * 本地环境运行
     * @param $schedule
     * @return void
     */
    private function runInLocal($schedule)
    {
        // APP_ENV=local # 可选值包括 local, production, testing, staging 等
        if (!$this->app->environment('local')) {
            return;
        }
        $schedule->command('sync:test')->everyThreeMinutes();
    }

    /**
     * 只在线上内网环境运行
     * @param $schedule
     */
    private function runInProdLocal($schedule)
    {
        if (!$this->app->environment('production_local')) {
            return;
        }
        // nothing
    }
    /**
     * 除了线上和本地环境其他都运行
     * @param $schedule
     */
    private function runInCommon($schedule)
    {
        if ($this->app->environment('production') || $this->app->environment('local')) {
            return;
        }
        $schedule->command('sync:test')->everyTenMinutes();
        $schedule->command('sync:test')->everyMinute();
        $schedule->command('sync:test')->dailyAt('05:00');
        // withoutOverlapping()关注的是任务在时间上的错开（防止自己打自己），而 onOneServer()关注的是任务在空间上的唯一性（防止多人干同一件事）
        $schedule->command('sync:test')->daily()->onOneServer();
        $schedule->command('sync:test')->everyTwoMinutes()->withoutOverlapping();
    }

    /**
     * 只在线上环境运行
     * @param $schedule
     * @return void
     */
    private function runInProduction($schedule)
    {
        if (!$this->app->environment('production')) {
            return;
        }
        $schedule->command('sync:test --minutes=5 --isCreate=2')->everyTwoMinutes()->withoutOverlapping();
        $schedule->command('sync:test')->everyThirtyMinutes()->between('6:00', '23:00');
        $schedule->command('sync:test')->everyTwoHours();
        $schedule->command('sync:test')->dailyAt('10:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
