<?php

declare(strict_types=1);

it('switches the locale and remembers it in the session', function (): void {
    $this->from('/')->post('/locale/fa')->assertRedirect('/')->assertSessionHas('locale', 'fa');

    $this->get('/')->assertHeader('Content-Language', 'fa');
});

it('rejects unsupported locales', function (): void {
    $this->post('/locale/xx')->assertNotFound();
});

it('falls back to the default locale', function (): void {
    $this->get('/')->assertHeader('Content-Language', config('app.locale'));
});
