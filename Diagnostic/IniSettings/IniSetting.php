<?php


namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings;


abstract class IniSetting
{
    /**
     * @var string
     */
    static public $key;

    /**
     * @var boolean
     */
    static public $targetValue;
    /**
     * @var bool
     */
    static public $severe = true;
    /**
     * @var string
     */
    static public $url;
}
