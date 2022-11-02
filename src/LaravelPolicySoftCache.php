<?php

namespace Innoge\LaravelPolicySoftCache;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Innoge\LaravelPolicySoftCache\Contracts\SoftCacheable;

class LaravelPolicySoftCache
{
    /**
     * @var array<string,mixed>
     */
    protected array $cache = [];

    /**
     * @throws BindingResolutionException
     */
    public static function flushCache(): void
    {
        app()->make(static::class)->cache = [];
    }

    public function handleGateCall(mixed $user, string $ability, mixed $args): mixed
    {
        if (!is_array($args)) {
            $args = [$args];
        }

        if (!($user instanceof Model)) {
            return null;
        }

        $model = $args[0] ?? null;

        $policy = Gate::getPolicyFor($model);

        if ($model && $this->shouldCache($policy)) {
            return $this->callPolicyMethod($user, $model, $policy, $ability, $args);
        }

        return null;
    }

    protected function shouldCache(?object $policy): bool
    {
        return $policy && ($policy instanceof SoftCacheable || config('policy-soft-cache.cache_all_policies', false) === true);
    }

    /**
     * @param Model $user
     * @param Model $model
     * @param object $policy
     * @param string $ability
     * @param array<int,mixed> $args
     * @return mixed
     */
    protected function callPolicyMethod(Model $user, Model $model, object $policy, string $ability, array $args): mixed
    {
        $cacheKey = $this->getCacheKey($user, $model, $ability);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = $policy->{$ability}(...array_merge([$user], $args));

        $this->cache[$cacheKey] = $result;

        return $result;
    }

    protected function getCacheKey(Model $user, Model $model, string $ability): string
    {
        return $user->{$user->getKeyName()}. '_' . $model::class . '_' . $ability;
    }
}
