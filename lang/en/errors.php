<?php

declare(strict_types=1);

return [
    403 => ['title' => 'Access denied', 'message' => 'You do not have permission to view this page.'],
    404 => ['title' => 'Page not found', 'message' => 'The page you are looking for does not exist or was moved.'],
    419 => ['title' => 'Page expired', 'message' => 'Your session expired. Please refresh the page and try again.'],
    429 => ['title' => 'Too many requests', 'message' => 'Please slow down and try again in a moment.'],
    500 => ['title' => 'Something went wrong', 'message' => 'An unexpected error occurred. Our team has been notified.'],
    503 => ['title' => 'Be right back', 'message' => 'We are doing some maintenance. Please check back soon.'],
];
