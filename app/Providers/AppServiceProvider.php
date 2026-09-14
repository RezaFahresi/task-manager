<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || env('VERCEL')) {
            Vite::useHotFile(storage_path('vite.hot'));
        }

        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new class($connection, $database, $prefix, $config) extends SQLiteConnection
            {
                protected function getDefaultQueryGrammar()
                {
                    return new class($this) extends SQLiteGrammar
                    {
                        protected function whereBasic(Builder $query, $where)
                        {
                            $isLike = in_array(strtolower($where['operator']), ['ilike', 'like'], true);
                            if (strtolower($where['operator']) === 'ilike') {
                                $where['operator'] = 'like';
                            }

                            $sql = parent::whereBasic($query, $where);

                            if ($isLike) {
                                $sql .= " escape '\\'";
                            }

                            return $sql;
                        }
                    };
                }
            };
        });
    }
}
