<?php

declare(strict_types=1);

namespace App\Modules\Blog\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Stringable;

/**
 * URL slug. Unicode letters are kept so Persian titles produce readable slugs
 * ("سلام دنیا" → "سلام-دنیا"). Castable: use `'slug' => Slug::class` in casts().
 */
final readonly class Slug implements Castable, Stringable
{
    public const int MAX_LENGTH = 200;

    private const string PATTERN = '/^[\p{Ll}\p{Lm}\p{Lo}\p{M}\p{N}]+(?:-[\p{Ll}\p{Lm}\p{Lo}\p{M}\p{N}]+)*$/u';

    public string $value;

    public function __construct(string $value)
    {
        if ($value === '' || mb_strlen($value) > self::MAX_LENGTH || preg_match(self::PATTERN, $value) !== 1) {
            throw new InvalidArgumentException("Invalid slug [{$value}].");
        }

        $this->value = $value;
    }

    public static function fromTitle(string $title): self
    {
        $slug = mb_rtrim(mb_substr(mb_strtolower(Str::slug(str_replace("\u{0640}", '', $title), '-', null)), 0, self::MAX_LENGTH), '-');

        return new self($slug === '' ? 'post' : $slug);
    }

    public static function isValid(string $value): bool
    {
        return $value !== '' && mb_strlen($value) <= self::MAX_LENGTH && preg_match(self::PATTERN, $value) === 1;
    }

    public function withSuffix(int $number): self
    {
        $suffix = '-'.$number;

        return new self(mb_rtrim(mb_substr($this->value, 0, self::MAX_LENGTH - mb_strlen($suffix)), '-').$suffix);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @param  array<mixed>  $arguments
     * @return CastsAttributes<Slug, Slug|string>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, mixed $value, array $attributes): ?Slug
            {
                return is_string($value) ? new Slug($value) : null;
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): ?string
            {
                return match (true) {
                    $value instanceof Slug => $value->value,
                    is_string($value) => (new Slug($value))->value,
                    default => null,
                };
            }
        };
    }
}
