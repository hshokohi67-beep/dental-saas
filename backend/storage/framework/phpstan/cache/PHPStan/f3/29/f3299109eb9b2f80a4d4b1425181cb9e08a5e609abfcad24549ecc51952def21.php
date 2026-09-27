<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-pdorow
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4-',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'PDORow',
        'filename' => 'phpstorm-stubs:PDO/PDO.stub',
        'extensionName' => 'PDO',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'PDORow',
    'shortName' => 'PDORow',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Represents a row from a result set returned by PDOStatement::fetch called with
 * PDO::FETCH_LAZY fetch mode.
 *
 * Objects of this class cannot be instantiated and are not serializable. The PDORow object
 * allows access to the returned data as if both PDO::FETCH_OBJ and PDO::FETCH_BOTH mode were
 * used. This means that the returned data can be accessed as object properties, and as an array
 * both indexed by the column name and a column offset number.
 *
 * @link https://php.net/manual/en/class.pdorow.php
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 21,
    'endLine' => 25,
    'startColumn' => 5,
    'endColumn' => 5,
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
      'queryString' => 
      array (
        'declaringClassName' => 'PDORow',
        'implementingClassName' => 'PDORow',
        'name' => 'queryString',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string\']',
                'attributes' => 
                array (
                  'startLine' => 23,
                  'endLine' => 23,
                  'startTokenPos' => 49,
                  'startFilePos' => 968,
                  'endTokenPos' => 55,
                  'endFilePos' => 986,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 23,
                  'endLine' => 23,
                  'startTokenPos' => 61,
                  'startFilePos' => 998,
                  'endTokenPos' => 61,
                  'endFilePos' => 999,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 23,
        'endLine' => 24,
        'startColumn' => 9,
        'endColumn' => 35,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
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