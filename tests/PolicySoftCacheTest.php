<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Innoge\LaravelPolicySoftCache\Contracts\SoftCacheable;

it('caches policy calls with SoftCacheable interface', function () {
    Config::set('policy-soft-cache.cache_all_policies', false);

    $user = new User();
    $testModel = new TestModel();
    $testModel->setAttribute('id', 1);

    Gate::policy(TestModel::class, PolicyWithSoftCache::class);

    $user->can('view', $testModel);
    $user->can('view', $testModel);

    expect(PolicyWithSoftCache::$called)->toBe(1);

    PolicyWithSoftCache::$called = 0;
    Config::set('policy-soft-cache.cache_all_policies', true);
});

it('does not cache policy calls without SoftCacheable interface', function () {
    Config::set('policy-soft-cache.cache_all_policies', false);

    $user = new User();
    $testModel = new TestModel();
    $testModel->setAttribute('id', 1);

    Gate::policy(TestModel::class, PolicyWithoutSoftCache::class);

    $user->can('view', $testModel);
    $user->can('view', $testModel);

    expect(PolicyWithoutSoftCache::$called)
        ->toBe(2);

    PolicyWithoutSoftCache::$called = 0;
    Config::set('policy-soft-cache.cache_all_policies', true);
});

it('caches all policy calls by default', function () {
    $user = new User();
    $testModel = new TestModel();
    $testModel->setAttribute('id', 1);

    Gate::policy(TestModel::class, PolicyWithoutSoftCache::class);

    $user->can('view', $testModel);
    $user->can('view', $testModel);

    expect(PolicyWithoutSoftCache::$called)->toBe(1);

    PolicyWithoutSoftCache::$called = 0;
});

it('does not break normal gate calls', function () {
    $user = new User();
    $user->can('foo');

    expect(true)->toBeTrue();
});

class PolicyWithSoftCache implements SoftCacheable
{
    public static int $called = 0;

    public function view(User $user, TestModel $model): bool
    {
        static::$called++;

        return true;
    }
}

class PolicyWithoutSoftCache
{
    public static int $called = 0;

    public function view(User $user, TestModel $model): bool
    {
        static::$called++;

        return true;
    }
}

class TestModel extends Model
{
}
