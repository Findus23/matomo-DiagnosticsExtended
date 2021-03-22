<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings;

class AllowUrlInclude extends IniSetting
{
    static public $key = "allow_url_include";

    public static $targetValue = false;

    public static $url = "https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-include";

}
