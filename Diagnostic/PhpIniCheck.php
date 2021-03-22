<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic;

use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings\IniSetting;
use Psr\Log\LoggerInterface;

class PhpIniCheck implements Diagnostic
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var IniSetting[]
     */
    private $iniSettings;

    public function __construct(array $iniSettings, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->iniSettings = $iniSettings;
    }

    public function execute()
    {
        $result = new DiagnosticResult("php.ini checks");
        foreach ($this->iniSettings as $setting) {
            $key = $setting::$key;
            if ($this->booleanIni($key) === $setting::$targetValue) {
                $item = new DiagnosticResultItem(
                    DiagnosticResult::STATUS_OK,
                    $setting::$targetValue ? "$key is enabled" : "$key is disabled"
                );

            } else {
                $status = $setting::$severe ? DiagnosticResult::STATUS_ERROR : DiagnosticResult::STATUS_WARNING;
                $item = new DiagnosticResultItem(
                    $status,
                    $setting::$targetValue ? "$key should be enabled" : "$key should be disabled"
                );
            }
            $result->addItem($item);
        }
        return array($result);
    }


    private function booleanIni(string $key): bool
    {
        return $this->IniValueToBoolean(ini_get($key));
    }

    private function IniValueToBoolean(string $iniValue): bool
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
