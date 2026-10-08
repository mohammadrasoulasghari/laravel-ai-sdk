<?php

declare(strict_types=1);

namespace App\Modules\Blog\Enums;

enum BlogPermission: string
{
    case ManagePosts = 'blog.posts.manage';
    case ManageCategories = 'blog.categories.manage';
}
