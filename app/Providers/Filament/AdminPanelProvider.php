<?php

namespace App\Providers\Filament;

use App\Filament\Resources\EmployeeResource\Widgets\EmployeeOverview;
use App\Filament\Resources\EmployeeResource\Widgets\LatestEmployees;
use App\Filament\Resources\EmployeeResource\Widgets\UserOverview;
use App\Filament\Resources\PoliciesResource\Widgets\PolicyDepartmentChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentWorldClock\FilamentWorldClockPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->profile()
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(asset('storage/img/logo.svg'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets')
            ->widgets([
                UserOverview::make(),
                LatestEmployees::make(),
                PolicyDepartmentChart::make(),
                EmployeeOverview::make()
                ,

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Employee Management')
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make()
                    ->label('System Management')
                    ->icon('heroicon-o-folder'),
            ])
            ->plugins([
                \Hasnayeen\Themes\ThemesPlugin::make(),
                FilamentWorldClockPlugin::make()
                    ->timezones([

                        'Africa/Cairo',
                        'Asia/Riyadh',
                        'Asia/Dubai',
                    ])
                    ->setTimeFormat('H:i') //Optional time format default is: 'H:i'
                    ->shouldShowTitle(false) //Optional show title default is: true
                    ->setTitle('Hours') //Optional title default is: 'World Clock'
                    ->setDescription('Different description') //Optional description default is: 'Show hours around the world by timezone'
                    ->setQuantityPerRow(1) //Optional quantity per row default is: 1
                    ->setColumnSpan('full') //Optional column span default is: '1/2'
                    ->setSort(8),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
