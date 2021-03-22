<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings;

class ExposePhp extends IniSetting
{
    static public $key = "expose_php";

    public static $targetValue = false;

    public static $severe = false;

    public static $url = "https://www.php.net/manual/de/security.hiding.php";

}
