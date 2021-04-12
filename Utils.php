<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


namespace Piwik\Plugins\DiagnosticsExtended;


class Utils
{
    static function booleanIni(string $key): bool
    {
        return Utils::IniValueToBoolean(ini_get($key));
    }

    static function intIni(string $key): int
    {
        return (int)ini_get($key);
    }

    static function IniValueToBoolean(string $iniValue): bool
    {
        switch (strtolower($iniValue)) {
            case "on":
            case "true":
            case "yes":
            case "1":
                return true;
            case "off":
            case "false":
            case "no":
            case "0":
            case "":
                return false;
            default:
                return $iniValue;
        }
    }

}
