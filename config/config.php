<?php
return [
    'diagnosticsExtended.inisettings' => DI\add([
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings\AllowUrlInclude'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings\DisplayErrors'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings\ExposePhp'),
    ]),
    '\Piwik\Plugins\DiagnosticsExtended\Diagnostic\PhpIniCheck' => DI\autowire()
        ->constructor(
            DI\get('diagnosticsExtended.inisettings')
        ),
    'diagnostics.optional' => DI\add([
//        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\ExampleCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\PhpIniCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\PhpVersionCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\DatabaseVersionCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\MatomoJsCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\CurlVersionCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\OpensslVersionCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\PhpUserCheck'),
        DI\get('\Piwik\Plugins\DiagnosticsExtended\Diagnostic\OpcacheCheck'),
    ]),

];
