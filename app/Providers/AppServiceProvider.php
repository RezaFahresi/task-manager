<?php

namespace App\Providers;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\SQLiteConnection;
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
        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new class($connection, $database, $prefix, $config) extends SQLiteConnection
            {
                protected function getDefaultQueryGrammar()
                {
                    return new class($this) extends SQLiteGrammar
                    {
                        protected function whereBasic(Builder $query, $where)
                        {
                            if (strtolower($where['operator']) === 'ilike') {
                                $where['operator'] = 'like';
                            }

                            return parent::whereBasic($query, $where);
                        }
                    };
                }
            };
        });
    }
}
