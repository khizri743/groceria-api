<?php

namespace App\Providers\Filament;

use Filament\Navigation\NavigationItem;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Enums\ThemeMode; // Import this

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            
            // 1. Force Light Mode (Makes it "Bright")
            // ->defaultThemeMode(ThemeMode::Light)
            ->darkMode(false) 

            

            
            ->renderHook(
            'panels::head.done',
            fn (): string => new \Illuminate\Support\HtmlString('
                <style>
                    /* Darken the horizontal lines in tables */
                    .fi-ta-content tr {
                        border-bottom: 1.5px solid #e2e8f0 !important; /* Slate-200 */
                    }
                    /* Darken the vertical lines if you use them */
                    .fi-ta-content td, .fi-ta-content th {
                        border-right: 1px solid #f1f5f9 !important;
                    }
                    /* Make the table header background slightly distinct */
                    .fi-ta-header-ctn {
                        background-color: #f8fafc !important;
                        border-bottom: 2px solid #cbd5e1 !important; /* Slate-300 */
                    }
                </style>
            '),
        )
        



            // 2. The Color Scheme
            ->colors([
                'primary' => Color::Emerald, // Fresh Green (Main Brand Color)
                'gray'    => Color::Slate,   // Cool, crisp gray (instead of muddy gray)
                'info'    => Color::Sky,     // Bright Blue for info
                'success' => Color::Teal,    // Distinct green for success
                'warning' => Color::Orange,  // Bright Orange for warnings
                'danger'  => Color::Rose,    // Soft Red for danger
            ])
            
            // 3. Optional: Make the font look modern
            ->font('Poppins') 
            
            // 4. Branding
            ->brandName('Groceria Admin')
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // We generated the custom StatsOverview, so we don't need default widgets
                // Widgets\AccountWidget::class, 
                // Widgets\FilamentInfoWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}