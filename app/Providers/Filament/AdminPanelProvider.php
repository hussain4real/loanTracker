<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Models\Team;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Platform;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // ->registration()
            // ->tenantRegistration(RegisterTeam::class)
            ->colors([
                'primary' => Color::Teal,
            ])
            ->brandName(config('app.name'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')

            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->widgets([
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
            ])
            ->spa(true)
            ->spaUrlExceptions(fn (): array => [
                // url('/admin/chat'),
                // url('/admin/livefeed'),
            ])
            ->globalSearchKeyBindings([
                'command+k',
                'ctrl+k',
            ])
            ->globalSearchDebounce('750ms')
            ->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Windows => 'CTRL+K',
                Platform::Linux => 'CTRL+K',
                Platform::Mac => '⌘+K',
                default => null,
            })
            ->databaseNotifications()
            ->databaseNotificationsPolling('120s')
            ->unsavedChangesAlerts(false)
            ->breadcrumbs(true)
            ->sidebarWidth('14rem')
            ->collapsedSidebarWidth('2rem')
            ->maxContentWidth(MaxWidth::Screen->value)
            ->sidebarCollapsibleOnDesktop(true)
            //            ->sidebarFullyCollapsibleOnDesktop(true)
            ->topNavigation(false)
            ->collapsibleNavigationGroups(true)
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false);        // ->tenant(Team::class);
    }

    public function register(): void
    {
        parent::register();
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@vite('resources/js/app.js')"),
        );
        // FilamentView::registerRenderHook(
        //     'before',
        //     fn (): string => '<html dir="'.(app()->getLocale() === 'ar' ? 'rtl' : 'ltr').'" lang="'.app()->getLocale().'">'
        // );
    }
}
