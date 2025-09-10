<?php

namespace App\Providers;

use App\Models\System\AdminLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    const TABLE_ACTIONS = [
        'created' => '创建',
        'updated' => '编辑',
        'saved' => '保存',
        'deleted' => '删除',
    ];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->sqlToLog();
        // $this->sqlToDb();
    }
    /**
     * 请求日志存入日志系统
     */
    private function sqlToLog()
    {
        if (!config('app.debug')) {
            return;
        }
        DB::listen(function ($query) {
            $sql = str_replace(['%', '?'], ["%%", "%s"], $query->sql);
            $log = @vsprintf($sql, array_map(function ($v) {
                return is_int($v) ? $v : "'$v'";
            }, $query->bindings)) ?: $sql;

            $log = "[{$query->time}ms] " . $log;
            $logDir = storage_path('logs/sql');
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            Log::channel('sql')->info($log);
        });
    }

    /**
     * 修改日志存入数据库
     */
    private function sqlToDb()
    {
        Event::listen('eloquent.*', function ($eventName, $data) {
            if (preg_match('/eloquent\.(.+):\s(.+)/', $eventName, $match) === 0) {
                return;
            }
            /** $match 的格式如下
            array (
                0 => 'eloquent.saved: App\\Models\\AdminUser',
                1 => 'booting',
                2 => 'App\\Models\\AdminUser',
            )
             */
            // 创建、修改、删除才记录
            if (!in_array($match[1], array_keys(self::TABLE_ACTIONS))) {
                return;
            }
            // 没有管理员信息不记录
            $admin = Auth::guard('api')->user();
            if (!$admin) {
                return;
            }
            // 获取模型
            $model = $data[0];
            // 不记录日志
            if (!$model->logRecord) {
                return;
            }
            // 未保存数据不处理
            if ($match[1] == 'saved' && $model->isDirty()) {
                return;
            }
            $diff = array_diff_assoc($model->getAttributes(), $model->getRawOriginal());
            $keys = array_keys($diff);

            // 挑选修改后的数据
            $data = [];
            foreach ($keys as $key) {
                if ($key === 'updated_at') {
                    continue;
                }
                if ($model->getRawOriginal($key) == $model->getAttributes()[$key]) {
                    continue;
                }
                $data[$key] = [
                    'old' => $model->getRawOriginal($key),
                    'new' => $model->getAttributes()[$key]
                ];
            }
            // 未更新任何数据
            if (in_array($match[1], ['updated', 'saved']) && empty($data)) {
                return;
            }
            AdminLog::query()->create([
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'url' => request()->fullUrl(),
                'type' => $model->tableName,
                'action' => self::TABLE_ACTIONS[$match[1]],
                'login_ip' => request()->getClientIp(),
                'model_id' => $model->id,
                'model_name' => get_class($model),
                'data' => $data,
                'created_at' => now(),
            ]);
        });
    }
}
