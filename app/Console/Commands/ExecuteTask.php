<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Log;
use Cron\CronExpression;
use Carbon\Carbon;
use Exception;
use DateTime;


/**
 * Класс ExecuteTask выполняет задачи по cron-расписанию.
 */
class ExecuteTask extends Command
{
    /**
     * Сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'task:execute';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Выполняет задачу из очереди по cron-расписанию';

    /**
     * Выполняет консольную команду.
     *
     * @return void
     */
    public function handle(): void
    {
        // Получаем текущее время
        $now = new DateTime();
        $this->info('Текущее время ' . $now->format('Y-m-d H:i:s'));

        // Получаем все задачи со статусом 'pending', которые были обновлены более минуты назад
        $tasks = Task::where('status', 'pending')
        ->where(function($query) {
            $query->whereNull('updated')
                  ->orWhere('updated', '<=', Carbon::now()->subMinute());
        })
        ->get();

        $this->info('Кол-во: ' . $tasks->count());

        foreach ($tasks as $task) {
            // Создаем объект CronExpression для проверки расписания
            $cron = new CronExpression($task->cron_expression);
            $this->info('Время для задачи: ' . $task->method_name . ' ' . $cron->getNextRunDate()->format('Y-m-d H:i:s'));

            // Проверяем, соответствует ли текущее время cron-расписанию
            if ($cron->isDue($now)) {
                $this->info('Проверяем задачу: ' . $task->method_name);

                // Пытаемся отметить задачу как выполняющуюся
                if ($task->markAsRunning()) {
                    try {
                        $this->info('Берём задачу: ' . $task->method_name);

                        // Выполняем логику задачи
                        $this->executeTaskLogic($task);

                        // Обновляем статус задачи на 'completed'
                        $task->markAsCompleted();

                        // Если задача не одноразовая, сбрасываем статус на 'pending' и обновляем updated_at
                        if (!$task->isOneTimeTask()) {
                            $task->resetToPending();
                        }

                        // Добавляем запись в таблицу log
                        $this->logTaskCompletion($task);

                    } catch (Exception $e) {
                        // В случае ошибки обновляем статус задачи на 'failed'
                        $task->markAsFailed();
                        $this->error('Ошибка выполнения задачи: ' . $e->getMessage());
                    }
                }
            }
        }
    }


    /**
     * Выполняет логику задачи.
     *
     * @param Task $task Задача для выполнения
     * @return void
     */
    private function executeTaskLogic(Task $task): void
    {
        // Выполняем логику задачи
        $this->info('Выполнение задачи: ' . $task->method_name);
    }

    /**
     * Логирует завершение задачи.
     *
     * @param Task $task Завершенная задача
     * @return void
     */
    private function logTaskCompletion(Task $task): void
    {
        Log::create([
            'task_id' => $task->id,
            'message' => 'Задание ' . $task->method_name. ' выполнено сервером ' . gethostname(),
        ]);
    }
}
