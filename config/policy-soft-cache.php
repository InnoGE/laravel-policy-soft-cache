<?php

return [
    /*
     * When enabled, the package will cache the results of all Policies in your Laravel application
     */
    'cache_all_policies' => false,

    /*
     * The prefix to use when caching the results of a Policy
     */
    'cache_prefix' => 'soft_cache_',

    /*
     * The tags to use when caching the results of a Policy
     */
    'cache_tags' => ['policy-soft-cache'],

    /*
     * The cache driver to use when caching the results of a Policy
     */
    'cache_driver' => 'array',
];
