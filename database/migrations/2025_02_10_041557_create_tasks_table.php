<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'stoped'])->default('pending')->comment('Статус задачи');
            $table->string('method_name')->comment('Имя метода для выполнения');
            $table->string('cron_expression')->comment('Cron-выражение для расписания');
            $table->boolean('is_one_time')->default(false)->comment('Флаг одноразовой задачи');
            $table->text('data')->comment('Данные задачи в формате JSON');
            $table->timestamp('finished')->nullable()->comment('Время завершения задачи');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
