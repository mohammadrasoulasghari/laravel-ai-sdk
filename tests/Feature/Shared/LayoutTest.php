<?php

declare(strict_types=1);

use App\Shared\Navigation\Navigation;
use App\Shared\Navigation\NavigationArea;
use App\Shared\Navigation\NavigationItem;

it('renders RTL markup for Persian', function (): void {
    $this->withSession(['locale' => 'fa'])->get('/')
        ->assertOk()
        ->assertSee('<html lang="fa" dir="rtl">', false)
        ->assertSee(__('ui.welcome_title', locale: 'fa'));
});

it('renders LTR markup for English', function (): void {
    $this->withSession(['locale' => 'en'])->get('/')
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr">', false)
        ->assertSee(__('ui.skip_to_content', locale: 'en'));
});

it('shows registered navigation items whose route exists', function (): void {
    $navigation = resolve(Navigation::class);
    $navigation->add(new NavigationItem(NavigationArea::App, 'ui.home', 'home'));
    $navigation->add(new NavigationItem(NavigationArea::App, 'ui.search', 'missing.route'));
    $navigation->add(new NavigationItem(NavigationArea::App, 'ui.admin_panel', 'home', ability: 'nobody-has-this'));

    $this->get('/')
        ->assertSee('aria-current="page"', false)
        ->assertDontSee(__('ui.search'))
        ->assertDontSee(__('ui.admin_panel'));
});

it('serves the style guide only outside production', function (): void {
    $this->get('/_styleguide')->assertOk()->assertSee(__('ui.styleguide.title'));

    app()->detectEnvironment(fn (): string => 'production');

    $this->get('/_styleguide')->assertNotFound();
});

it('renders custom error pages', function (int $status): void {
    $this->withSession(['locale' => 'en']);

    $this->get('/definitely-missing')->assertNotFound()->assertSee(__('errors.404.title'));
})->with([404]);

it('renders every error view', function (int $code): void {
    $html = view("errors.{$code}")->render();

    expect($html)->toContain((string) $code)->toContain(__("errors.{$code}.title"));
})->with([403, 404, 419, 429, 500, 503]);
