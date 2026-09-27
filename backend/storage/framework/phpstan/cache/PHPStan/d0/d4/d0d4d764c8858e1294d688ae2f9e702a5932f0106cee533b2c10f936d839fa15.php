<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-soapclient
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4-',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'SoapClient',
        'filename' => 'phpstorm-stubs:soap/soap.stub',
        'extensionName' => 'soap',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'SoapClient',
    'shortName' => 'SoapClient',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * The SoapClient class provides a client for SOAP 1.1, SOAP 1.2 servers. It can be used in WSDL
 * or non-WSDL mode.
 * @link https://php.net/manual/en/class.soapclient.php
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 9,
    'endLine' => 408,
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
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'wsdl' => 
          array (
            'name' => 'wsdl',
            'default' => NULL,
            'type' => 
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
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
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
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 136,
                      'endLine' => 136,
                      'startTokenPos' => 26,
                      'startFilePos' => 5397,
                      'endTokenPos' => 32,
                      'endFilePos' => 5420,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 136,
                      'endLine' => 136,
                      'startTokenPos' => 38,
                      'startFilePos' => 5432,
                      'endTokenPos' => 38,
                      'endFilePos' => 5433,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 136,
            'endLine' => 137,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 138,
                'endLine' => 138,
                'startTokenPos' => 55,
                'startFilePos' => 5497,
                'endTokenPos' => 56,
                'endFilePos' => 5498,
              ),
            ),
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
            'startLine' => 138,
            'endLine' => 138,
            'startColumn' => 13,
            'endColumn' => 31,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * SoapClient constructor
 * @link https://php.net/manual/en/soapclient.construct.php
 * @param string|null $wsdl <p>
 * URI of the WSDL file or <b>NULL</b> if working in
 * non-WSDL mode.
 * </p>
 * <p>
 * During development, WSDL caching may be disabled by the
 * use of the soap.wsdl_cache_ttl <i>php.ini</i> setting
 * otherwise changes made to the WSDL file will have no effect until
 * soap.wsdl_cache_ttl is expired.
 * </p>
 * @param array $options [optional] <p>
 * An array of options. If working in WSDL mode, this parameter is optional.
 * If working in non-WSDL mode, the location and
 * uri options must be set, where location
 * is the URL of the SOAP server to send the request to, and uri
 * is the target namespace of the SOAP service.
 * </p>
 * <p>
 * The style and use options only work in
 * non-WSDL mode. In WSDL mode, they come from the WSDL file.
 * </p>
 * <p>
 * The soap_version option should be one of either
 * <b>SOAP_1_1</b> or <b>SOAP_1_2</b> to
 * select SOAP 1.1 or 1.2, respectively. If omitted, 1.1 is used.
 * </p>
 * <p>
 * For HTTP authentication, the login and
 * password options can be used to supply credentials.
 * For making an HTTP connection through
 * a proxy server, the options proxy_host,
 * proxy_port, proxy_login
 * and proxy_password are also available.
 * For HTTPS client certificate authentication use
 * local_cert and passphrase options. An
 * authentication may be supplied in the authentication
 * option. The authentication method may be either
 * <b>SOAP_AUTHENTICATION_BASIC</b> (default) or
 * <b>SOAP_AUTHENTICATION_DIGEST</b>.
 * </p>
 * <p>
 * The compression option allows to use compression
 * of HTTP SOAP requests and responses.
 * </p>
 * <p>
 * The encoding option defines internal character
 * encoding. This option does not change the encoding of SOAP requests (it is
 * always utf-8), but converts strings into it.
 * </p>
 * <p>
 * The trace option enables tracing of request so faults
 * can be backtraced. This defaults to <b>FALSE</b>
 * </p>
 * <p>
 * The classmap option can be used to map some WSDL
 * types to PHP classes. This option must be an array with WSDL types
 * as keys and names of PHP classes as values.
 * </p>
 * <p>
 * Setting the boolean trace option enables use of the
 * methods
 * SoapClient->__getLastRequest,
 * SoapClient->__getLastRequestHeaders,
 * SoapClient->__getLastResponse and
 * SoapClient->__getLastResponseHeaders.
 * </p>
 * <p>
 * The exceptions option is a boolean value defining whether
 * soap errors throw exceptions of type
 * SoapFault.
 * </p>
 * <p>
 * The connection_timeout option defines a timeout in seconds
 * for the connection to the SOAP service. This option does not define a timeout
 * for services with slow responses. To limit the time to wait for calls to finish the
 * default_socket_timeout setting
 * is available.
 * </p>
 * <p>
 * The typemap option is an array of type mappings.
 * Type mapping is an array with keys type_name,
 * type_ns (namespace URI), from_xml
 * (callback accepting one string parameter) and to_xml
 * (callback accepting one object parameter).
 * </p>
 * <p>
 * The cache_wsdl option is one of
 * <b>WSDL_CACHE_NONE</b>,
 * <b>WSDL_CACHE_DISK</b>,
 * <b>WSDL_CACHE_MEMORY</b> or
 * <b>WSDL_CACHE_BOTH</b>.
 * </p>
 * <p>
 * The user_agent option specifies string to use in
 * User-Agent header.
 * </p>
 * <p>
 * The stream_context option is a resource
 * for context.
 * </p>
 * <p>
 * The features option is a bitmask of
 * <b>SOAP_SINGLE_ELEMENT_ARRAYS</b>,
 * <b>SOAP_USE_XSI_ARRAY_TYPE</b>,
 * <b>SOAP_WAIT_ONE_WAY_CALLS</b>.
 * </p>
 * <p>
 * The keep_alive option is a boolean value defining whether
 * to send the Connection: Keep-Alive header or
 * Connection: close.
 * </p>
 * <p>
 * The ssl_method option is one of
 * <b>SOAP_SSL_METHOD_TLS</b>,
 * <b>SOAP_SSL_METHOD_SSLv2</b>,
 * <b>SOAP_SSL_METHOD_SSLv3</b> or
 * <b>SOAP_SSL_METHOD_SSLv23</b>.
 * </p>
 * @throws SoapFault A SoapFault exception will be thrown if the wsdl URI cannot be loaded.
 * @since 5.0
 */',
        'startLine' => 135,
        'endLine' => 141,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__call' => 
      array (
        'name' => '__call',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
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
                      'startLine' => 166,
                      'endLine' => 166,
                      'startTokenPos' => 84,
                      'startFilePos' => 7010,
                      'endTokenPos' => 90,
                      'endFilePos' => 7028,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 166,
                      'endLine' => 166,
                      'startTokenPos' => 96,
                      'startFilePos' => 7040,
                      'endTokenPos' => 96,
                      'endFilePos' => 7041,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 166,
            'endLine' => 167,
            'startColumn' => 13,
            'endColumn' => 24,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'args' => 
          array (
            'name' => 'args',
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
            'startLine' => 168,
            'endLine' => 168,
            'startColumn' => 13,
            'endColumn' => 23,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
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
 * Calls a SOAP function (deprecated)
 *
 * Calling this method directly is deprecated. Usually, SOAP functions can be called as methods
 * of the SoapClient object; in situations where this is not possible or additional options are
 * needed, use SoapClient::__soapCall.
 *
 * @link https://php.net/manual/en/soapclient.call.php
 * @param string $name The name of the SOAP function to call.
 * @param array $args An array of the arguments to pass to the function. This can be either an
 * ordered or an associative array. Note that most SOAP servers require parameter names to be
 * provided, in which case this must be an associative array.
 * @return mixed SOAP functions may return one, or multiple values. If only one value is
 * returned by the SOAP function, the return value will be a scalar. If multiple values are
 * returned, an associative array of named output parameters is returned instead. On error, if
 * the SoapClient object was constructed with the exceptions option set to false, a SoapFault
 * object will be returned.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 * @deprecated
 */',
        'startLine' => 163,
        'endLine' => 171,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__soapCall' => 
      array (
        'name' => '__soapCall',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
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
                      'startLine' => 221,
                      'endLine' => 221,
                      'startTokenPos' => 134,
                      'startFilePos' => 9507,
                      'endTokenPos' => 140,
                      'endFilePos' => 9525,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 221,
                      'endLine' => 221,
                      'startTokenPos' => 146,
                      'startFilePos' => 9537,
                      'endTokenPos' => 146,
                      'endFilePos' => 9538,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 221,
            'endLine' => 222,
            'startColumn' => 13,
            'endColumn' => 24,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'args' => 
          array (
            'name' => 'args',
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
            'startLine' => 223,
            'endLine' => 223,
            'startColumn' => 13,
            'endColumn' => 23,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 225,
                'endLine' => 225,
                'startTokenPos' => 187,
                'startFilePos' => 9732,
                'endTokenPos' => 187,
                'endFilePos' => 9735,
              ),
            ),
            'type' => 
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
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
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
                    'code' => '[\'8.0\' => \'array|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 224,
                      'endLine' => 224,
                      'startTokenPos' => 163,
                      'startFilePos' => 9659,
                      'endTokenPos' => 169,
                      'endFilePos' => 9681,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 224,
                      'endLine' => 224,
                      'startTokenPos' => 175,
                      'startFilePos' => 9693,
                      'endTokenPos' => 175,
                      'endFilePos' => 9694,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 224,
            'endLine' => 225,
            'startColumn' => 13,
            'endColumn' => 38,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'inputHeaders' => 
          array (
            'name' => 'inputHeaders',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 226,
                'endLine' => 226,
                'startTokenPos' => 194,
                'startFilePos' => 9766,
                'endTokenPos' => 194,
                'endFilePos' => 9769,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 226,
            'endLine' => 226,
            'startColumn' => 13,
            'endColumn' => 32,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'outputHeaders' => 
          array (
            'name' => 'outputHeaders',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 227,
                'endLine' => 227,
                'startTokenPos' => 202,
                'startFilePos' => 9802,
                'endTokenPos' => 202,
                'endFilePos' => 9805,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => true,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 227,
            'endLine' => 227,
            'startColumn' => 13,
            'endColumn' => 34,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
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
        ),
        'docComment' => '/**
 * Calls a SOAP function
 * @link https://php.net/manual/en/soapclient.soapcall.php
 * @param string $name <p>
 * The name of the SOAP function to call.
 * </p>
 * @param array $args <p>
 * An array of the arguments to pass to the function. This can be either
 * an ordered or an associative array. Note that most SOAP servers require
 * parameter names to be provided, in which case this must be an
 * associative array.
 * </p>
 * @param array $options [optional] <p>
 * An associative array of options to pass to the client.
 * </p>
 * <p>
 * The location option is the URL of the remote Web service.
 * </p>
 * <p>
 * The uri option is the target namespace of the SOAP service.
 * </p>
 * <p>
 * The soapaction option is the action to call.
 * </p>
 * @param mixed $inputHeaders [optional] <p>
 * An array of headers to be sent along with the SOAP request.
 * </p>
 * @param array &$outputHeaders [optional] <p>
 * If supplied, this array will be filled with the headers from the SOAP response.
 * </p>
 * @return mixed SOAP functions may return one, or multiple values. If only one value is returned
 * by the SOAP function, the return value of __soapCall will be
 * a simple value (e.g. an integer, a string, etc). If multiple values are
 * returned, __soapCall will return
 * an associative array of named output parameters.
 * </p>
 * <p>
 * On error, if the SoapClient object was constructed with the exceptions
 * option set to <b>FALSE</b>, a SoapFault object will be returned. If this
 * option is not set, or is set to <b>TRUE</b>, then a SoapFault object will
 * be thrown as an exception.
 * @throws SoapFault A SoapFault exception will be thrown if an error occurs
 * and the SoapClient was constructed with the exceptions option not set, or
 * set to <b>TRUE</b>.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 219,
        'endLine' => 230,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getLastRequest' => 
      array (
        'name' => '__getLastRequest',
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
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Returns last SOAP request
 * @link https://php.net/manual/en/soapclient.getlastrequest.php
 * @return string|null The last SOAP request, as an XML string.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 238,
        'endLine' => 241,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getLastResponse' => 
      array (
        'name' => '__getLastResponse',
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
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Returns last SOAP response
 * @link https://php.net/manual/en/soapclient.getlastresponse.php
 * @return string|null The last SOAP response, as an XML string.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 249,
        'endLine' => 252,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getLastRequestHeaders' => 
      array (
        'name' => '__getLastRequestHeaders',
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
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Returns the SOAP headers from the last request
 * @link https://php.net/manual/en/soapclient.getlastrequestheaders.php
 * @return string|null The last SOAP request headers.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 260,
        'endLine' => 263,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getLastResponseHeaders' => 
      array (
        'name' => '__getLastResponseHeaders',
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
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Returns the SOAP headers from the last response
 * @link https://php.net/manual/en/soapclient.getlastresponseheaders.php
 * @return string|null The last SOAP response headers.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 271,
        'endLine' => 274,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getFunctions' => 
      array (
        'name' => '__getFunctions',
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
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Returns list of available SOAP functions
 * @link https://php.net/manual/en/soapclient.getfunctions.php
 * @return array|null The array of SOAP function prototypes, detailing the return type,
 * the function name and type-hinted parameters.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 283,
        'endLine' => 286,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getTypes' => 
      array (
        'name' => '__getTypes',
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
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Returns a list of SOAP types
 * @link https://php.net/manual/en/soapclient.gettypes.php
 * @return array|null The array of SOAP types, detailing all structures and types.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 294,
        'endLine' => 297,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__getCookies' => 
      array (
        'name' => '__getCookies',
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
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Returns a list of all cookies
 * @link https://php.net/manual/en/soapclient.getcookies.php
 * @return array The array of all cookies
 * @since 5.4
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 305,
        'endLine' => 308,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__doRequest' => 
      array (
        'name' => '__doRequest',
        'parameters' => 
        array (
          'request' => 
          array (
            'name' => 'request',
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
                      'startLine' => 337,
                      'endLine' => 337,
                      'startTokenPos' => 382,
                      'startFilePos' => 13942,
                      'endTokenPos' => 388,
                      'endFilePos' => 13960,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 337,
                      'endLine' => 337,
                      'startTokenPos' => 394,
                      'startFilePos' => 13972,
                      'endTokenPos' => 394,
                      'endFilePos' => 13973,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 337,
            'endLine' => 338,
            'startColumn' => 13,
            'endColumn' => 27,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'location' => 
          array (
            'name' => 'location',
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
                      'startLine' => 339,
                      'endLine' => 339,
                      'startTokenPos' => 406,
                      'startFilePos' => 14072,
                      'endTokenPos' => 412,
                      'endFilePos' => 14090,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 339,
                      'endLine' => 339,
                      'startTokenPos' => 418,
                      'startFilePos' => 14102,
                      'endTokenPos' => 418,
                      'endFilePos' => 14103,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 339,
            'endLine' => 340,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'action' => 
          array (
            'name' => 'action',
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
                      'startLine' => 341,
                      'endLine' => 341,
                      'startTokenPos' => 430,
                      'startFilePos' => 14203,
                      'endTokenPos' => 436,
                      'endFilePos' => 14221,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 341,
                      'endLine' => 341,
                      'startTokenPos' => 442,
                      'startFilePos' => 14233,
                      'endTokenPos' => 442,
                      'endFilePos' => 14234,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 341,
            'endLine' => 342,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'version' => 
          array (
            'name' => 'version',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
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
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 343,
                      'endLine' => 343,
                      'startTokenPos' => 454,
                      'startFilePos' => 14332,
                      'endTokenPos' => 460,
                      'endFilePos' => 14347,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 343,
                      'endLine' => 343,
                      'startTokenPos' => 466,
                      'startFilePos' => 14359,
                      'endTokenPos' => 466,
                      'endFilePos' => 14360,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 343,
            'endLine' => 344,
            'startColumn' => 13,
            'endColumn' => 24,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
          'oneWay' => 
          array (
            'name' => 'oneWay',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 346,
                'endLine' => 346,
                'startTokenPos' => 500,
                'startFilePos' => 14519,
                'endTokenPos' => 500,
                'endFilePos' => 14523,
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
                    'code' => '["8.0" => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 345,
                      'endLine' => 345,
                      'startTokenPos' => 478,
                      'startFilePos' => 14456,
                      'endTokenPos' => 484,
                      'endFilePos' => 14472,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'int\'',
                    'attributes' => 
                    array (
                      'startLine' => 345,
                      'endLine' => 345,
                      'startTokenPos' => 490,
                      'startFilePos' => 14484,
                      'endTokenPos' => 490,
                      'endFilePos' => 14488,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 345,
            'endLine' => 346,
            'startColumn' => 13,
            'endColumn' => 32,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
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
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Performs a SOAP request
 * @link https://php.net/manual/en/soapclient.dorequest.php
 * @param string $request <p>
 * The XML SOAP request.
 * </p>
 * @param string $location <p>
 * The URL to request.
 * </p>
 * @param string $action <p>
 * The SOAP action.
 * </p>
 * @param int $version <p>
 * The SOAP version.
 * </p>
 * @param bool|int $oneWay [optional] <p>
 * If $oneWay is set to 1, this method returns nothing.
 * Use this where a response is not expected.
 * </p>
 * @param string|null $uriParserClass The classname to use for parsing the redirection URI when
 * a "Location" header is received in the response, or null to use the default, parse_url based
 * parsing.
 * @return string|null The XML SOAP response.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 335,
        'endLine' => 349,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__setCookie' => 
      array (
        'name' => '__setCookie',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
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
                      'startLine' => 365,
                      'endLine' => 365,
                      'startTokenPos' => 528,
                      'startFilePos' => 15195,
                      'endTokenPos' => 534,
                      'endFilePos' => 15213,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 365,
                      'endLine' => 365,
                      'startTokenPos' => 540,
                      'startFilePos' => 15225,
                      'endTokenPos' => 540,
                      'endFilePos' => 15226,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 365,
            'endLine' => 366,
            'startColumn' => 13,
            'endColumn' => 24,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 368,
                'endLine' => 368,
                'startTokenPos' => 576,
                'startFilePos' => 15401,
                'endTokenPos' => 576,
                'endFilePos' => 15404,
              ),
            ),
            'type' => 
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
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
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
                    'code' => '["8.0" => "string|null"]',
                    'attributes' => 
                    array (
                      'startLine' => 367,
                      'endLine' => 367,
                      'startTokenPos' => 552,
                      'startFilePos' => 15322,
                      'endTokenPos' => 558,
                      'endFilePos' => 15345,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '"string"',
                    'attributes' => 
                    array (
                      'startLine' => 367,
                      'endLine' => 367,
                      'startTokenPos' => 564,
                      'startFilePos' => 15357,
                      'endTokenPos' => 564,
                      'endFilePos' => 15364,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 367,
            'endLine' => 368,
            'startColumn' => 13,
            'endColumn' => 37,
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
        ),
        'docComment' => '/**
 * The __setCookie purpose
 * @link https://php.net/manual/en/soapclient.setcookie.php
 * @param string $name <p>
 * The name of the cookie.
 * </p>
 * @param string $value [optional] <p>
 * The value of the cookie. If not specified, the cookie will be deleted.
 * </p>
 * @return void No value is returned.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 363,
        'endLine' => 371,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__setLocation' => 
      array (
        'name' => '__setLocation',
        'parameters' => 
        array (
          'location' => 
          array (
            'name' => 'location',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 385,
                'endLine' => 385,
                'startTokenPos' => 627,
                'startFilePos' => 16048,
                'endTokenPos' => 627,
                'endFilePos' => 16051,
              ),
            ),
            'type' => 
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
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
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
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 384,
                      'endLine' => 384,
                      'startTokenPos' => 603,
                      'startFilePos' => 15972,
                      'endTokenPos' => 609,
                      'endFilePos' => 15995,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 384,
                      'endLine' => 384,
                      'startTokenPos' => 615,
                      'startFilePos' => 16007,
                      'endTokenPos' => 615,
                      'endFilePos' => 16008,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 384,
            'endLine' => 385,
            'startColumn' => 13,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
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
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
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
 * Sets the location of the Web service to use
 * @link https://php.net/manual/en/soapclient.setlocation.php
 * @param string $location [optional] <p>
 * The new endpoint URL.
 * </p>
 * @return string|null The old endpoint URL.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 382,
        'endLine' => 388,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
        'aliasName' => NULL,
      ),
      '__setSoapHeaders' => 
      array (
        'name' => '__setSoapHeaders',
        'parameters' => 
        array (
          'headers' => 
          array (
            'name' => 'headers',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 404,
                'endLine' => 404,
                'startTokenPos' => 666,
                'startFilePos' => 16844,
                'endTokenPos' => 666,
                'endFilePos' => 16847,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
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
                    'code' => '\'7.0\'',
                    'attributes' => 
                    array (
                      'startLine' => 403,
                      'endLine' => 403,
                      'startTokenPos' => 658,
                      'startFilePos' => 16813,
                      'endTokenPos' => 658,
                      'endFilePos' => 16817,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 403,
            'endLine' => 404,
            'startColumn' => 13,
            'endColumn' => 27,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
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
        ),
        'docComment' => '/**
 * Sets SOAP headers for subsequent calls
 * @link https://php.net/manual/en/soapclient.setsoapheaders.php
 * @param mixed $headers <p>
 * The headers to be set. It could be <b>SoapHeader</b>
 * object or array of <b>SoapHeader</b> objects.
 * If not specified or set to <b>NULL</b>, the headers will be deleted.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 * @since 5.0
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 401,
        'endLine' => 407,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'SoapClient',
        'implementingClassName' => 'SoapClient',
        'currentClassName' => 'SoapClient',
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