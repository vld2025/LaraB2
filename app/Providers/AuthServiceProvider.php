<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Report;
use App\Models\Cliente;
use App\Models\Cantiere;
use App\Models\Commessa;
use App\Models\Spesa;
use App\Models\SpesaExtra;
use App\Models\Fattura;
use App\Policies\UserPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ClientePolicy;
use App\Policies\CantierePolicy;
use App\Policies\CommessaPolicy;
use App\Policies\SpesaPolicy;
use App\Policies\SpesaExtraPolicy;
use App\Policies\FatturaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Report::class => ReportPolicy::class,
        Cliente::class => ClientePolicy::class,
        Cantiere::class => CantierePolicy::class,
        Commessa::class => CommessaPolicy::class,
        Spesa::class => SpesaPolicy::class,
        SpesaExtra::class => SpesaExtraPolicy::class,
        Fattura::class => FatturaPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
