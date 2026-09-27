<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-datetimeinterface
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4-',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'DateTimeInterface',
        'filename' => 'phpstorm-stubs:date/date_c.stub',
        'extensionName' => 'date',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'DateTimeInterface',
    'shortName' => 'DateTimeInterface',
    'isInterface' => true,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * DateTimeInterface was created so that parameter, return, or property type declarations may accept
 * either DateTimeImmutable or DateTime as a value. It is not possible to implement this interface
 * with userland classes.
 *
 * Common constants that allow for formatting DateTimeImmutable or DateTime objects through
 * DateTimeImmutable::format and DateTime::format are also defined on this interface.
 *
 * @link https://php.net/manual/en/class.datetimeinterface.php
 * @since 5.5
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 15,
    'endLine' => 183,
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
      'ATOM' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'ATOM',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'Y-m-d\\TH:i:sP\'',
          'attributes' => 
          array (
            'startLine' => 20,
            'endLine' => 20,
            'startTokenPos' => 24,
            'startFilePos' => 670,
            'endTokenPos' => 24,
            'endFilePos' => 684,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 20,
        'endLine' => 20,
        'startColumn' => 9,
        'endColumn' => 44,
      ),
      'COOKIE' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'COOKIE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'l, d-M-Y H:i:s T\'',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 24,
            'startTokenPos' => 37,
            'startFilePos' => 763,
            'endTokenPos' => 37,
            'endFilePos' => 780,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 24,
        'startColumn' => 9,
        'endColumn' => 49,
      ),
      'ISO8601' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'ISO8601',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'Y-m-d\\TH:i:sO\'',
          'attributes' => 
          array (
            'startLine' => 31,
            'endLine' => 31,
            'startTokenPos' => 50,
            'startFilePos' => 1069,
            'endTokenPos' => 50,
            'endFilePos' => 1083,
          ),
        ),
        'docComment' => '/**
 * This format is not compatible with ISO-8601, but is left this way for backward compatibility reasons.
 * Use DateTime::ATOM or DATE_ATOM for compatibility with ISO-8601 instead.
 * @since 7.2
 * 
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 31,
        'endLine' => 31,
        'startColumn' => 9,
        'endColumn' => 47,
      ),
      'ISO8601_EXPANDED' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'ISO8601_EXPANDED',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\\DATE_ISO8601_EXPANDED',
          'attributes' => 
          array (
            'startLine' => 35,
            'endLine' => 35,
            'startTokenPos' => 63,
            'startFilePos' => 1172,
            'endTokenPos' => 63,
            'endFilePos' => 1193,
          ),
        ),
        'docComment' => '/**
 * @since 8.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 35,
        'endLine' => 35,
        'startColumn' => 9,
        'endColumn' => 63,
      ),
      'RFC822' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC822',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'D, d M y H:i:s O\'',
          'attributes' => 
          array (
            'startLine' => 39,
            'endLine' => 39,
            'startTokenPos' => 76,
            'startFilePos' => 1272,
            'endTokenPos' => 76,
            'endFilePos' => 1289,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 39,
        'endLine' => 39,
        'startColumn' => 9,
        'endColumn' => 49,
      ),
      'RFC850' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC850',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'l, d-M-y H:i:s T\'',
          'attributes' => 
          array (
            'startLine' => 43,
            'endLine' => 43,
            'startTokenPos' => 89,
            'startFilePos' => 1368,
            'endTokenPos' => 89,
            'endFilePos' => 1385,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 43,
        'endLine' => 43,
        'startColumn' => 9,
        'endColumn' => 49,
      ),
      'RFC1036' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC1036',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'D, d M y H:i:s O\'',
          'attributes' => 
          array (
            'startLine' => 47,
            'endLine' => 47,
            'startTokenPos' => 102,
            'startFilePos' => 1465,
            'endTokenPos' => 102,
            'endFilePos' => 1482,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 47,
        'endLine' => 47,
        'startColumn' => 9,
        'endColumn' => 50,
      ),
      'RFC1123' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC1123',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'D, d M Y H:i:s O\'',
          'attributes' => 
          array (
            'startLine' => 51,
            'endLine' => 51,
            'startTokenPos' => 115,
            'startFilePos' => 1562,
            'endTokenPos' => 115,
            'endFilePos' => 1579,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 51,
        'endLine' => 51,
        'startColumn' => 9,
        'endColumn' => 50,
      ),
      'RFC2822' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC2822',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'D, d M Y H:i:s O\'',
          'attributes' => 
          array (
            'startLine' => 55,
            'endLine' => 55,
            'startTokenPos' => 128,
            'startFilePos' => 1659,
            'endTokenPos' => 128,
            'endFilePos' => 1676,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 55,
        'endLine' => 55,
        'startColumn' => 9,
        'endColumn' => 50,
      ),
      'RFC3339' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC3339',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'Y-m-d\\TH:i:sP\'',
          'attributes' => 
          array (
            'startLine' => 59,
            'endLine' => 59,
            'startTokenPos' => 141,
            'startFilePos' => 1756,
            'endTokenPos' => 141,
            'endFilePos' => 1770,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 59,
        'endLine' => 59,
        'startColumn' => 9,
        'endColumn' => 47,
      ),
      'RFC3339_EXTENDED' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC3339_EXTENDED',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'Y-m-d\\TH:i:s.vP\'',
          'attributes' => 
          array (
            'startLine' => 63,
            'endLine' => 63,
            'startTokenPos' => 154,
            'startFilePos' => 1859,
            'endTokenPos' => 154,
            'endFilePos' => 1875,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 63,
        'endLine' => 63,
        'startColumn' => 9,
        'endColumn' => 58,
      ),
      'RFC7231' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RFC7231',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'D, d M Y H:i:s \\G\\M\\T\'',
          'attributes' => 
          array (
            'startLine' => 68,
            'endLine' => 68,
            'startTokenPos' => 177,
            'startFilePos' => 2011,
            'endTokenPos' => 177,
            'endFilePos' => 2033,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              'since' => 
              array (
                'code' => '\'8.5\'',
                'attributes' => 
                array (
                  'startLine' => 67,
                  'endLine' => 67,
                  'startTokenPos' => 165,
                  'startFilePos' => 1972,
                  'endTokenPos' => 165,
                  'endFilePos' => 1976,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 67,
        'endLine' => 68,
        'startColumn' => 9,
        'endColumn' => 55,
      ),
      'RSS' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'RSS',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'D, d M Y H:i:s O\'',
          'attributes' => 
          array (
            'startLine' => 72,
            'endLine' => 72,
            'startTokenPos' => 190,
            'startFilePos' => 2109,
            'endTokenPos' => 190,
            'endFilePos' => 2126,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 72,
        'endLine' => 72,
        'startColumn' => 9,
        'endColumn' => 46,
      ),
      'W3C' => 
      array (
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'name' => 'W3C',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'Y-m-d\\TH:i:sP\'',
          'attributes' => 
          array (
            'startLine' => 76,
            'endLine' => 76,
            'startTokenPos' => 203,
            'startFilePos' => 2202,
            'endTokenPos' => 203,
            'endFilePos' => 2216,
          ),
        ),
        'docComment' => '/**
 * @since 7.2
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 76,
        'endLine' => 76,
        'startColumn' => 9,
        'endColumn' => 43,
      ),
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'diff' => 
      array (
        'name' => 'diff',
        'parameters' => 
        array (
          'targetObject' => 
          array (
            'name' => 'targetObject',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DateTimeInterface',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 91,
            'endLine' => 91,
            'startColumn' => 13,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'absolute' => 
          array (
            'name' => 'absolute',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 93,
                'endLine' => 93,
                'startTokenPos' => 251,
                'startFilePos' => 3083,
                'endTokenPos' => 251,
                'endFilePos' => 3087,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
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
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 92,
                      'endLine' => 92,
                      'startTokenPos' => 229,
                      'startFilePos' => 3021,
                      'endTokenPos' => 235,
                      'endFilePos' => 3037,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 92,
                      'endLine' => 92,
                      'startTokenPos' => 241,
                      'startFilePos' => 3049,
                      'endTokenPos' => 241,
                      'endFilePos' => 3050,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 92,
            'endLine' => 93,
            'startColumn' => 13,
            'endColumn' => 34,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DateInterval',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 5 &gt;=5.5.0)<br/>
 * Returns the difference between two DateTime objects
 * @link https://php.net/manual/en/datetime.diff.php
 * @param DateTimeInterface $targetObject <p>The date to compare to.</p>
 * @param bool $absolute <p>Should the interval be forced to be positive?</p>
 * @return DateInterval
 * The https://secure.php.net/manual/en/class.dateinterval.php DateInterval} object representing the
 * difference between the two dates.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 89,
        'endLine' => 94,
        'startColumn' => 9,
        'endColumn' => 25,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      'format' => 
      array (
        'name' => 'format',
        'parameters' => 
        array (
          'format' => 
          array (
            'name' => 'format',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 110,
                      'endLine' => 110,
                      'startTokenPos' => 282,
                      'startFilePos' => 3833,
                      'endTokenPos' => 288,
                      'endFilePos' => 3851,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 110,
                      'endLine' => 110,
                      'startTokenPos' => 294,
                      'startFilePos' => 3863,
                      'endTokenPos' => 294,
                      'endFilePos' => 3864,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 110,
            'endLine' => 111,
            'startColumn' => 13,
            'endColumn' => 26,
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
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Pure',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '\\true',
                'attributes' => 
                array (
                  'startLine' => 107,
                  'endLine' => 107,
                  'startTokenPos' => 264,
                  'startFilePos' => 3674,
                  'endTokenPos' => 264,
                  'endFilePos' => 3677,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 5 &gt;=5.5.0)<br/>
 * Returns date formatted according to given format
 * @link https://php.net/manual/en/datetime.format.php
 * @param string $format <p>
 * Format accepted by  {@link https://php.net/manual/en/function.date.php date()}.
 * </p>
 * @return string
 * Returns the formatted date string on success.
 * Prior to PHP 8.1, <b>FALSE</b> was returned on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 107,
        'endLine' => 112,
        'startColumn' => 9,
        'endColumn' => 18,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      'getOffset' => 
      array (
        'name' => 'getOffset',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
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
                'code' => '["8.0" => "int"]',
                'attributes' => 
                array (
                  'startLine' => 122,
                  'endLine' => 122,
                  'startTokenPos' => 313,
                  'startFilePos' => 4355,
                  'endTokenPos' => 319,
                  'endFilePos' => 4370,
                ),
              ),
              'default' => 
              array (
                'code' => '"int|false"',
                'attributes' => 
                array (
                  'startLine' => 122,
                  'endLine' => 122,
                  'startTokenPos' => 325,
                  'startFilePos' => 4382,
                  'endTokenPos' => 325,
                  'endFilePos' => 4392,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 5 &gt;=5.5.0)<br/>
 * Returns the timezone offset
 * @link https://php.net/manual/en/datetime.getoffset.php
 * @return int|false
 * Returns the timezone offset in seconds from UTC on success.
 * Prior to PHP 8.0, <b>FALSE</b> was returned on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 122,
        'endLine' => 124,
        'startColumn' => 9,
        'endColumn' => 36,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      'getTimestamp' => 
      array (
        'name' => 'getTimestamp',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'int\']',
                'attributes' => 
                array (
                  'startLine' => 134,
                  'endLine' => 134,
                  'startTokenPos' => 351,
                  'startFilePos' => 4898,
                  'endTokenPos' => 357,
                  'endFilePos' => 4913,
                ),
              ),
              'default' => 
              array (
                'code' => '\'int|false\'',
                'attributes' => 
                array (
                  'startLine' => 134,
                  'endLine' => 134,
                  'startTokenPos' => 363,
                  'startFilePos' => 4925,
                  'endTokenPos' => 363,
                  'endFilePos' => 4935,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 5 &gt;=5.5.0)<br/>
 * Gets the Unix timestamp
 * @link https://php.net/manual/en/datetime.gettimestamp.php
 * @return int
 * Returns the Unix timestamp representing the date.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 133,
        'endLine' => 135,
        'startColumn' => 9,
        'endColumn' => 39,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      'getTimezone' => 
      array (
        'name' => 'getTimezone',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'DateTimeZone',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 5 &gt;=5.5.0)<br/>
 * Return time zone relative to given DateTime
 * @link https://php.net/manual/en/datetime.gettimezone.php
 * @return DateTimeZone|false
 * Returns a {@link https://php.net/manual/en/class.datetimezone.php DateTimeZone} object on success
 * or <b>FALSE</b> on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 145,
        'endLine' => 146,
        'startColumn' => 9,
        'endColumn' => 59,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      '__wakeup' => 
      array (
        'name' => '__wakeup',
        'parameters' => 
        array (
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              'since' => 
              array (
                'code' => '\'8.5\'',
                'attributes' => 
                array (
                  'startLine' => 155,
                  'endLine' => 155,
                  'startTokenPos' => 408,
                  'startFilePos' => 5868,
                  'endTokenPos' => 408,
                  'endFilePos' => 5872,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * (PHP 5 &gt;=5.5.0)<br/>
 * The __wakeup handler
 * @link https://php.net/manual/en/datetime.wakeup.php
 * @return void Initializes a DateTime object.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 154,
        'endLine' => 156,
        'startColumn' => 9,
        'endColumn' => 41,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      '__serialize' => 
      array (
        'name' => '__serialize',
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.2\'',
                'attributes' => 
                array (
                  'startLine' => 165,
                  'endLine' => 165,
                  'startTokenPos' => 432,
                  'startFilePos' => 6254,
                  'endTokenPos' => 432,
                  'endFilePos' => 6258,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Serialize a DateTime
 *
 * The __serialize() handler.
 *
 * @link https://php.net/manual/en/datetime.serialize.php
 * @return array The serialized representation of the DateTime object.
 */',
        'startLine' => 165,
        'endLine' => 166,
        'startColumn' => 9,
        'endColumn' => 45,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      '__unserialize' => 
      array (
        'name' => '__unserialize',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 176,
            'endLine' => 176,
            'startColumn' => 39,
            'endColumn' => 49,
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'from' => 
              array (
                'code' => '\'8.2\'',
                'attributes' => 
                array (
                  'startLine' => 175,
                  'endLine' => 175,
                  'startTokenPos' => 456,
                  'startFilePos' => 6627,
                  'endTokenPos' => 456,
                  'endFilePos' => 6631,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Unserialize an Datetime
 *
 * The __unserialize() handler.
 *
 * @link https://php.net/manual/en/datetime.unserialize.php
 * @param array $data The serialized DateTime.
 */',
        'startLine' => 175,
        'endLine' => 176,
        'startColumn' => 9,
        'endColumn' => 57,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
        'aliasName' => NULL,
      ),
      'getMicrosecond' => 
      array (
        'name' => 'getMicrosecond',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Gets the microsecond part of the Unix timestamp
 * @link https://php.net/manual/en/datetimeinterface.getmicrosecond.php
 * @since 8.4
 */',
        'startLine' => 182,
        'endLine' => 182,
        'startColumn' => 9,
        'endColumn' => 46,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DateTimeInterface',
        'implementingClassName' => 'DateTimeInterface',
        'currentClassName' => 'DateTimeInterface',
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