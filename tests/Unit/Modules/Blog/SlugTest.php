<?php

declare(strict_types=1);

use App\Modules\Blog\Models\Post;
use App\Modules\Blog\ValueObjects\Slug;

describe('construction', function (): void {
    it('accepts valid slugs', function (string $value): void {
        expect((new Slug($value))->value)->toBe($value)
            ->and(Slug::isValid($value))->toBeTrue();
    })->with([
        'single word' => 'laravel',
        'hyphenated' => 'hello-world',
        'with digits' => 'release-2026',
        'persian' => 'سلام-دنیا',
        'persian digits' => 'نسخه-۲',
        'exactly 200 characters' => str_repeat('a', Slug::MAX_LENGTH),
        '200 persian characters' => str_repeat('س', Slug::MAX_LENGTH),
    ]);

    it('rejects invalid slugs', function (string $value): void {
        expect(Slug::isValid($value))->toBeFalse()
            ->and(fn (): Slug => new Slug($value))->toThrow(InvalidArgumentException::class, "Invalid slug [{$value}].");
    })->with([
        'empty' => '',
        'uppercase' => 'Hello',
        'whitespace' => 'hello world',
        'leading hyphen' => '-hello',
        'trailing hyphen' => 'hello-',
        'double hyphen' => 'hello--world',
        'underscore' => 'hello_world',
        'slash' => 'blog/hello',
        'html' => '<b>',
        '201 characters' => str_repeat('a', Slug::MAX_LENGTH + 1),
    ]);

    it('compares by value', function (): void {
        expect((new Slug('hello'))->equals(new Slug('hello')))->toBeTrue()
            ->and((new Slug('hello'))->equals(new Slug('world')))->toBeFalse();
    });

    it('casts to its value as a string', function (): void {
        $slug = new Slug('سلام-دنیا');

        expect($slug)->toBeInstanceOf(Stringable::class)
            ->and((string) $slug)->toBe('سلام-دنیا');
    });
});

describe('fromTitle', function (): void {
    it('builds a slug from a title', function (string $title, string $expected): void {
        expect(Slug::fromTitle($title)->value)->toBe($expected);
    })->with([
        'english' => ['Hello World', 'hello-world'],
        'punctuation is dropped' => ['Laravel 13: What’s New?', 'laravel-13-whats-new'],
        'surrounding and repeated whitespace' => ['  many   spaces  ', 'many-spaces'],
        'persian is kept, not transliterated' => ['سلام دنیا', 'سلام-دنیا'],
        'mixed scripts' => ['Laravel در ایران', 'laravel-در-ایران'],
        'persian digits' => ['نسخه ۲', 'نسخه-۲'],
        'zero-width non-joiner is removed' => ['دسته‌بندی', 'دستهبندی'],
    ]);

    it('falls back to "post" when nothing usable is left', function (string $title): void {
        expect(Slug::fromTitle($title)->value)->toBe('post');
    })->with([
        'empty' => '',
        'only symbols' => '!!! ??? ...',
        'only whitespace' => '   ',
    ]);

    it('caps the length at 200 characters', function (): void {
        expect(Slug::fromTitle(str_repeat('a', 250))->value)->toBe(str_repeat('a', Slug::MAX_LENGTH));
    });

    it('never ends a truncated slug with a hyphen', function (): void {
        // 199 letters + " b" slugifies to 201 characters whose 200th is the separator.
        $slug = Slug::fromTitle(str_repeat('a', 199).' b');

        expect($slug->value)->toBe(str_repeat('a', 199));
    });

    it('accepts titles containing modifier letters', function (string $title, string $expected): void {
        expect(Slug::fromTitle($title)->value)->toBe($expected);
    })->with([
        'persian tatweel (U+0640)' => ['سلامـدنیا', 'سلامدنیا'],
        'katakana long vowel mark (U+30FC)' => ['コーヒー', 'コーヒー'],
    ]);
});

describe('withSuffix', function (): void {
    it('appends a numeric suffix', function (): void {
        expect((new Slug('hello-world'))->withSuffix(2)->value)->toBe('hello-world-2')
            ->and((new Slug('سلام-دنیا'))->withSuffix(3)->value)->toBe('سلام-دنیا-3');
    });

    it('trims the base so the result still fits in 200 characters', function (int $suffix): void {
        $suffixed = (new Slug(str_repeat('a', Slug::MAX_LENGTH)))->withSuffix($suffix);

        expect(mb_strlen($suffixed->value))->toBe(Slug::MAX_LENGTH)
            ->and($suffixed->value)->toEndWith('a-'.$suffix);
    })->with([2, 10, 1000]);

    it('never produces a double hyphen when trimming the base', function (): void {
        // Trimming to 198 characters leaves "aaa…a-", which then gets "-2" appended.
        $slug = new Slug(str_repeat('a', 197).'-b');

        expect($slug->withSuffix(2)->value)->toBe(str_repeat('a', 197).'-2');
    });
});

describe('Eloquent cast', function (): void {
    it('hydrates strings from the database into Slug objects', function (): void {
        $cast = Slug::castUsing([]);
        $slug = $cast->get(new Post, 'slug', 'hello-world', []);

        expect($slug)->toBeInstanceOf(Slug::class)
            ->and($slug?->value)->toBe('hello-world')
            ->and($cast->get(new Post, 'slug', null, []))->toBeNull();
    });

    it('stores Slug objects and valid strings as plain strings', function (mixed $value, ?string $stored): void {
        expect(Slug::castUsing([])->set(new Post, 'slug', $value, []))->toBe($stored);
    })->with([
        'slug object' => [new Slug('hello-world'), 'hello-world'],
        'valid string' => ['سلام-دنیا', 'سلام-دنیا'],
        'null' => [null, null],
    ]);

    it('refuses to store an invalid string', function (): void {
        expect(fn (): ?string => Slug::castUsing([])->set(new Post, 'slug', 'Not A Slug', []))
            ->toThrow(InvalidArgumentException::class);
    });
});
