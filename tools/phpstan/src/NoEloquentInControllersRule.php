<?php

declare(strict_types=1);

namespace Tools\PHPStan;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Controllers are thin: validate → Action/Query → response. They must not query or
 * persist Eloquent models themselves (CLAUDE.md golden rule #2, ADR-0005).
 *
 * Allowed: route-model binding type hints, reading attributes, and explicit
 * eager loading (load / loadMissing / loadCount) of an already-bound model.
 *
 * @implements Rule<Node\Expr\CallLike>
 */
final class NoEloquentInControllersRule implements Rule
{
    private const array PERSISTENCE_METHODS = [
        'save', 'saveQuietly', 'update', 'updateQuietly', 'delete', 'deleteQuietly', 'forceDelete',
        'restore', 'push', 'increment', 'decrement', 'fill', 'forceFill', 'touch', 'replicate',
        'newQuery', 'query', 'where', 'firstOrCreate', 'updateOrCreate', 'create', 'refresh',
    ];

    public function getNodeType(): string
    {
        return Node\Expr\CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->inController($scope)) {
            return [];
        }

        if ($node instanceof StaticCall && $node->class instanceof Name) {
            $class = $scope->resolveName($node->class);

            if ((new ObjectType(Model::class))->isSuperTypeOf(new ObjectType($class))->yes()) {
                return [$this->error(sprintf('static call %s::%s()', $class, $this->name($node->name)))];
            }

            return [];
        }

        if (! $node instanceof MethodCall) {
            return [];
        }

        $type = $scope->getType($node->var);

        foreach ([EloquentBuilder::class, QueryBuilder::class, Relation::class] as $builder) {
            if ((new ObjectType($builder))->isSuperTypeOf($type)->yes()) {
                return [$this->error('query builder call ->'.$this->name($node->name).'()')];
            }
        }

        $method = $this->name($node->name);

        if ((new ObjectType(Model::class))->isSuperTypeOf($type)->yes() && in_array($method, self::PERSISTENCE_METHODS, true)) {
            return [$this->error('model persistence/query call ->'.$method.'()')];
        }

        return [];
    }

    private function inController(Scope $scope): bool
    {
        $class = $scope->getClassReflection()?->getName() ?? '';

        return str_contains($class, '\\Http\\Controllers\\');
    }

    private function name(Node $name): string
    {
        return $name instanceof Identifier ? $name->toString() : '{dynamic}';
    }

    private function error(string $what): \PHPStan\Rules\IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            'Controllers must not touch Eloquent directly (%s). Move it into an Action (writes) or a Query object (reads).',
            $what,
        ))->identifier('starter.eloquentInController')->build();
    }
}
