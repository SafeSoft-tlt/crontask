<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Класс Log представляет запись в журнале.
 */
class Log extends Model
{
    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = ['task_id', 'message'];
}
