<?php declare(strict_types = 1);

// odsl-C:\Projects\dental-final\backend\app\Domain\Identity\Support\DefaultRoles.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Domain\Identity\Support\DefaultRoles
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.73.0.5-8.4-d6ab0010eb6fffdedaea72c7aa93c51d9a04f8208fde1b3d3302099c37edb3cc',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'filename' => 'C:/Projects/dental-final/backend/app/Domain/Identity/Support/DefaultRoles.php',
      ),
    ),
    'namespace' => 'App\\Domain\\Identity\\Support',
    'name' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
    'shortName' => 'DefaultRoles',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * The initial role set every new tenant gets (master prompt §14). Tenants may
 * add further custom roles later — this class only seeds the starting point.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 13,
    'endLine' => 68,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'map' => 
      array (
        'name' => 'map',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return array<string, list<string>>
 */',
        'startLine' => 18,
        'endLine' => 48,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Domain\\Identity\\Support',
        'declaringClassName' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'implementingClassName' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'currentClassName' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'aliasName' => NULL,
      ),
      'seedForTenant' => 
      array (
        'name' => 'seedForTenant',
        'parameters' => 
        array (
          'tenantId' => 
          array (
            'name' => 'tenantId',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 55,
            'endLine' => 55,
            'startColumn' => 42,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Seed the default roles + their permissions scoped to one tenant (team).
 * Permissions are global (guard-scoped, not team-scoped) — only roles and
 * a model\'s role/permission assignments are team-scoped by spatie\'s teams feature.
 */',
        'startLine' => 55,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Domain\\Identity\\Support',
        'declaringClassName' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'implementingClassName' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'currentClassName' => 'App\\Domain\\Identity\\Support\\DefaultRoles',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));