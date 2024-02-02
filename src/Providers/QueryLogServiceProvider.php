<?php

namespace Gazzoy\LaravelLoggingMore\Providers;

use Carbon\Carbon as CarbonCarbon;
use Exception;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class QueryLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        DB::listen(function ($query)
        {
            $sql = $query->sql;

            if ($this->isIgnorableQuery($sql))
            {
                return;
            }

            foreach ($query->bindings as $eachBinding)
            {
                $theColumnName = $this->detectColumnNameFromSQL($sql);

                if (blank($theColumnName) || blank($eachBinding))
                {
                    continue;
                }

                if (
                    $theColumnName !== 'password_updated_at'
                    && Str::contains($theColumnName, 'password')
                )
                {
                    $eachBinding = '********';
                }

                switch (gettype($eachBinding))
                {
                    case 'integer':
                    case 'double':
                    case 'NULL':
                    case 'boolean':
                        $sql = preg_replace('/\\?/', $eachBinding, $sql, 1);

                        break;

                    case 'string':
                        $sql = preg_replace('/\\?/', "'{$eachBinding}'", $sql, 1);

                        break;

                    case 'object':
                    default:
                        $class = get_class($eachBinding);

                        switch ($class)
                        {
                            case \Illuminate\Support\Stringable::class:
                                $sql = preg_replace('/\\?/', "'{$eachBinding}'", $sql, 1);
                                break;
                            case 'DateTime':
                            case Carbon::class:
                            case CarbonCarbon::class:
                                $sql = preg_replace('/\\?/', "'" . date_format($eachBinding, 'Y-m-d') . "'", $sql, 1);

                                break;

                            default:
                                throw new \RuntimeException("Unexpected binding argument class ({$class})");
                        }

                        break;
                }
            }

            $this->writeLog($sql);
        });

        Event::listen(TransactionBeginning::class, function (TransactionBeginning $event): void
        {
            $this->writeLog('begin transaction');
        });

        Event::listen(TransactionCommitted::class, function (TransactionCommitted $event): void
        {
            $this->writeLog('commit transaction');
        });

        Event::listen(TransactionRolledBack::class, function (TransactionRolledBack $event): void
        {
            $this->writeLog('rollback transaction');
        });
    }

    private function writeLog($msg)
    {
        if (app()->isProduction() || config('app.env') === 'development')
        {
            try
            {
                Log::channel('query')->debug($msg);
            }
            catch (Exception $e)
            {
                // catch and ignore
                // echo 'Got exception:' . $e->getMessage();
            }
        }
    }

    private function isIgnorableQuery(string $sql)
    {
        if (str(request()->path())->startsWith('horizon/'))
        {
            return true;
        }

        // note: ignore session
        if (str($sql)->startsWith('update `sessions` set '))
        {
            return true;
        }

        // note: ignore pulse
        if (str($sql)->startsWith('insert into `pulse_') || str($sql)->contains('from `pulse_'))
        {
            return true;
        }

        // note: ignore telescope
        if (str($sql)->startsWith('insert into `telescope_') || str($sql)->contains('from `telescope_'))
        {
            return true;
        }

        return false;
    }

    public function detectColumnNameFromSQL($sql)
    {
        // note: detect first ? then replace chars with `
        $theColumnName = null;
        $tmp = explode('= ?', $sql);
        if (isset($tmp[0]))
        {
            $tmp = $tmp[0];
            // note: trim space as select*from`sessions`where`id`
            $tmp = Str::remove(' ', $tmp);

            /**
             * Build array by exploding with `
             * [
             * 0 => "select*from"
             * 1 => "sessions"
             * 2 => "where"
             * 3 => "id"
             * 4 => ""
             * ]
             */
            $tmp = explode('`', $tmp);
            // note: Reverse the order and guess that the next character after the blank character is the column name.
            $tmp = array_reverse($tmp);

            $isColumnNameBegins = false;
            foreach ($tmp as $k => $v)
            {
                if ($v == '')
                {
                    $isColumnNameBegins = true;

                    continue;
                }
                if ($isColumnNameBegins)
                {
                    return $v;
                }
            }
        }

        return null;
    }
}
