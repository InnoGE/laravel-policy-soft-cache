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
        if (! is_array($args)) {
            $args = [$args];
        }

        if (! ($user instanceof Model)) {
            return null;
        }

        $model = $args[0] ?? null;

        $policy = Gate::getPolicyFor($model);

        if ($model && $this->shouldCache($policy, $ability)) {
            return $this->callPolicyMethod($user, $policy, $ability, $args);
        }

        return null;
    }

    protected function shouldCache(?object $policy, string $ability): bool
    {
        // when policy is not filled don't cache it
        if (blank($policy)) {
            return false;
        }

        // when policy is not an object don't cache it
        if (! is_object($policy)) {
            return false;
        }

        // when policy doesn't have the ability don't cache it
        if (! method_exists($policy, $ability)) {
            return false;
        }

        // when policy is soft cacheable cache it
        if ($policy instanceof SoftCacheable) {
            return true;
        }

        // when config is set to cache all policies cache it
        return config('policy-soft-cache.cache_all_policies', false) === true;
    }

    /**
     * @param  array<int,mixed>  $args
     */
    protected function callPolicyMethod(Model $user, object $policy, string $ability, array $args): mixed
    {
        $cacheKey = $this->getCacheKey($user, $policy, $args, $ability);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = $policy->{$ability}(...array_merge([$user], $args));
        $this->cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * @param  array<int,mixed>  $args
     */
    protected function getCacheKey(Model $user, object $policy, array $args, string $ability): string
    {
        return $user->{$user->getKeyName()}.'_'.hash_hmac('sha512', (string) json_encode($args), config('app.key')).'_'.$ability.'_'.$policy::class;
    }
}
