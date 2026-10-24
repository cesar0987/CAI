<?php

declare(strict_types=1);

use Cai\Identity\Authorization\FeatureFlag;
use Cai\Identity\Authorization\FeatureFlagCatalog;

it('defines feature flags with a valid scope and toggled state', function () {
    $flag = new FeatureFlag('module.billing', 'module', 'Facturación', true);

    expect($flag->key)->toBe('module.billing');
    expect($flag->scope)->toBe('module');
    expect($flag->isEnabled())->toBeTrue();
    expect($flag->matches('module.billing'))->toBeTrue();
    expect($flag->withEnabled(false)->isEnabled())->toBeFalse();
});

it('exposes the canonical catalog of kill switches for the platform', function () {
    $flags = FeatureFlagCatalog::all();

    expect(array_keys($flags))->toContain('module.billing');
    expect(array_keys($flags))->toContain('module.care');
    expect(array_keys($flags))->toContain('feature.clinical_grants');
    expect($flags['module.billing']->scope)->toBe('module');
    expect($flags['feature.portal_families']->scope)->toBe('experiment');
});
