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

it('does not cache policy calls when ability does not exist', function () {
    Config::set('policy-soft-cache.cache_all_policies', false);

    $user = new User();
    $testModel = new TestModel();
    $testModel->setAttribute('id', 1);

    Gate::policy(TestModel::class, PolicyWithSoftCache::class);

    $user->can('foo', $testModel);
    $user->can('foo', $testModel);

    expect(PolicyWithSoftCache::$called)->toBe(0);

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

it('does not break if the action does not require a model instance', function () {
    $user = new User();

    Gate::policy(TestModel::class, PolicyWithoutRequiredModel::class);

    $user->can('create', [TestModel::class, 1]);

    expect(true)->toBeTrue();
});

it('caches correct value if it depends on the user class', function () {
    $user = new User();
    $customUser = new CustomUser();
    $testModel = new TestModel();

    Gate::policy(TestModel::class, PolicyWithDifferingCustomUserLogic::class);

    $userCanView = $user->can('view', $testModel);
    expect($userCanView)->toBeTrue();

    $customUserCanView = $customUser->can('view', $testModel);
    expect($customUserCanView)->toBeFalse();
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

class PolicyWithoutRequiredModel
{
    public function create(User $user, int $value): bool
    {
        return true;
    }
}

class TestModel extends Model
{
}

class CustomUser extends User
{
}

class PolicyWithDifferingCustomUserLogic implements SoftCacheable
{
    public function view(User|CustomUser $user, TestModel $model): bool
    {
        if ($user instanceof CustomUser) {
            return false;
        }

        return true;
    }
}
