<?php

declare(strict_types=1);

namespace Cai\Identity\Authorization;

final class FeatureFlagCatalog
{
    /**
     * @return array<string, FeatureFlag>
     */
    public static function all(): array
    {
        return [
            'module.billing' => new FeatureFlag('module.billing', 'module', 'Apaga todo el módulo de facturación', false),
            'module.care' => new FeatureFlag('module.care', 'module', 'Apaga el área clínica', false),
            'module.reporting' => new FeatureFlag('module.reporting', 'module', 'Apaga los reportes pesados', false),
            'module.notifications' => new FeatureFlag('module.notifications', 'module', 'Corta el envío de avisos', false),
            'feature.clinical_grants' => new FeatureFlag('feature.clinical_grants', 'feature', 'Impide conceder nuevos grants', false),
            'feature.auto_charge_generation' => new FeatureFlag('feature.auto_charge_generation', 'feature', 'Detiene la generación automática de cargos', false),
            'feature.portal_families' => new FeatureFlag('feature.portal_families', 'experiment', 'Portal de familias (etapa 2)', false),
        ];
    }

    public static function enabled(string $key): bool
    {
        return self::all()[$key]->isEnabled() ?? false;
    }
}
