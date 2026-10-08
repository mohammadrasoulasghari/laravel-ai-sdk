<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

use function Tests\modules;
use function Tests\root;

/*
| Models hold relations, casts, scopes and accessors only — no business logic.
| Every public method a model declares must be one of those (or a framework hook).
*/
it('keeps business logic out of Eloquent models', function (): void {
    $allowedNames = ['casts', 'newFactory', 'getRouteKeyName', 'resolveRouteBinding', 'broadcastOn', 'toSearchableArray', 'getMorphClass'];
    $violations = [];

    foreach (modules() as $module) {
        foreach (glob(root("app/Modules/{$module}/Models/*.php")) ?: [] as $file) {
            $class = "App\\Modules\\{$module}\\Models\\".basename($file, '.php');
            $reflection = new ReflectionClass($class);

            if (! $reflection->isSubclassOf(Model::class)) {
                continue;
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $class || $method->getFileName() !== $reflection->getFileName() || $method->isStatic() && $method->getName() === 'booted') {
                    continue;
                }

                $return = $method->getReturnType();
                $returnName = $return instanceof ReflectionNamedType ? $return->getName() : '';

                $isRelation = $returnName !== '' && is_a($returnName, Relation::class, true);
                $isAccessor = $returnName === Attribute::class;
                $isScope = $method->getAttributes(Scope::class) !== [] || str_starts_with($method->getName(), 'scope');
                $isAllowed = in_array($method->getName(), $allowedNames, true);

                if (! $isRelation && ! $isAccessor && ! $isScope && ! $isAllowed) {
                    $violations[] = "{$class}::{$method->getName()}()";
                }
            }
        }
    }

    expect($violations)->toBe([], 'Move these methods into an Action, Query or ValueObject');
});
