<?php

declare(strict_types=1);

namespace App\Modules\Blog\Enums;

/** Allowed values for ?sort= on post listings ("-" prefix = descending). */
enum PostSort: string
{
    case NewestFirst = '-published_at';
    case OldestFirst = 'published_at';
    case TitleAsc = 'title';
    case TitleDesc = '-title';

    public function column(): string
    {
        return mb_ltrim($this->value, '-');
    }

    /** @return 'asc'|'desc' */
    public function direction(): string
    {
        return str_starts_with($this->value, '-') ? 'desc' : 'asc';
    }
}
