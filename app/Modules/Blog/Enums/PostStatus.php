<?php

declare(strict_types=1);

namespace App\Modules\Blog\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
