<?php

declare(strict_types=1);

return [
    'nav' => [
        'dashboard' => 'Dashboard',
        'users' => 'Users',
    ],
    'dashboard' => [
        'title' => 'Dashboard',
        'greeting' => 'Welcome back, :name!',
        'body' => 'You are signed in. Build your first module with /feature or php artisan make:module.',
        'admin_link' => 'Open admin panel',
    ],
    'admin' => [
        'title' => 'Users',
        'edit_title' => 'Edit :name',
        'search_placeholder' => 'Search by name or email',
        'name' => 'Name',
        'email' => 'Email',
        'roles' => 'Roles',
        'joined' => 'Joined',
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        'delete_confirm' => 'Delete :name? Their API tokens are revoked too.',
    ],
    'updated' => 'User updated.',
    'deleted' => 'User deleted.',
    'errors' => [
        'cannot_delete_self' => 'You cannot delete your own account.',
    ],
];
