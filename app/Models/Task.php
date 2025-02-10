<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Класс Task представляет задачу в системе.
 */
class Task extends Model
{
    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = ['status', 'method_name', 'cron_expression', 'is_one_time', 'updated'];

    /**
     * Получает задачу для выполнения.
     *
     * @return Task|null
     */
    public static function getTaskForExecution(): ?Task
    {
        return self::where('status', 'pending')
            ->lockForUpdate()
            ->first();
    }

    /**
     * Отмечает задачу как выполняющуюся.
     *
     * @return bool
     */
    public function markAsRunning(): bool
    {
        return $this->where('id', $this->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'running']) > 0;
    }

    /**
     * Отмечает задачу как завершённую.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    /**
     * Отмечает задачу как ошибочную.
     *
     * @return bool
     */
    public function markAsFailed(): bool
    {
        return $this->update(['status' => 'failed']);
    }

    /**
     * Сбрасывает статус задачи на pending.
     *
     * @return bool
     */
    public function resetToPending(): bool
    {
        return $this->update(['status' => 'pending', 'updated' => Carbon::now()->subMinute()]);
    }

    /**
     * Проверяет, является ли задача одноразовой.
     *
     * @return bool
     */
    public function isOneTimeTask(): bool
    {
        return $this->is_one_time;
    }
}
