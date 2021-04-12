<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic;

use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\Plugins\DiagnosticsExtended\Diagnostic\IniSettings\IniSetting;
use Piwik\Plugins\DiagnosticsExtended\Utils;
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
    /**
     * @var string
     */
    private $label;

    public function __construct(array $iniSettings, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->iniSettings = $iniSettings;
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_PhpIniCheckLabel");
    }

    public function execute()
    {
        $result = new DiagnosticResult($this->label);
        foreach ($this->iniSettings as $setting) {
            $key = $setting::$key;
            if (Utils::booleanIni($key) === $setting::$targetValue) {
                $item = new DiagnosticResultItem(
                    DiagnosticResult::STATUS_OK,
                    $setting::$targetValue
                        ? Piwik::translate("DiagnosticsExtended_PhpIniCheckIsEnabled", [$key])
                        : Piwik::translate("DiagnosticsExtended_PhpIniCheckIsDisabled", [$key])
                );

            } else {
                $status = $setting::$severe ? DiagnosticResult::STATUS_ERROR : DiagnosticResult::STATUS_WARNING;
                $item = new DiagnosticResultItem(
                    $status,
                    $setting::$targetValue
                        ? Piwik::translate("DiagnosticsExtended_PhpIniCheckShouldBeEnabled", [$key])
                        : Piwik::translate("DiagnosticsExtended_PhpIniCheckShouldBeDisabled", [$key])
                );
            }
            $result->addItem($item);
        }
        return array($result);
    }




}
