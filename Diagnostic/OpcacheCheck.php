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
use Piwik\Plugins\DiagnosticsExtended\Utils;
use Psr\Log\LoggerInterface;

class OpcacheCheck implements Diagnostic
{

    private const MEGABYTE = 1024 * 1024;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $label;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->label = "🧪 " . "OPcache";
    }

    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        $result = new DiagnosticResult($this->label);
        if (!Utils::booleanIni("opcache.enable")) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckOpcacheDisabled")
            ));
            return [$result];
        } else {
            $status = opcache_get_status();
            $memoryUsage = $status["memory_usage"];
            $interned = $status["interned_strings_usage"];
            $statistics = $status["opcache_statistics"];
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_OK,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckOpcacheEnabled", [
                    round($memoryUsage["used_memory"] / self::MEGABYTE),
                    round(($memoryUsage["used_memory"] + $memoryUsage["free_memory"]) / self::MEGABYTE),
                    round($memoryUsage["current_wasted_percentage"] * 100, 2),
                    round($interned["used_memory"] / self::MEGABYTE),
                    round($interned["buffer_size"] / self::MEGABYTE),
                    round($statistics["opcache_hit_rate"])
                ])
            ));
        }
        if (!Utils::booleanIni("opcache.save_comments")) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_ERROR,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckSaveComments")
            ));
        }
        $minimum_files = 7963;
        if (Utils::intIni("opcache.max_accelerated_files") <= $minimum_files) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckMaxFiles", [$minimum_files])
            ));
        }
        if (Utils::intIni("opcache.memory_consumption") < 128) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckMemory")
            ));
        }
        if (Utils::intIni("opcache.interned_strings_buffer") < 8) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckInternedStrings")
            ));
        }
        if (!Utils::intIni("opcache.validate_timestamps")) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_INFORMATIONAL,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckValidateTimestamps")
            ));

        }
        $jit = ini_get("opcache.jit");
        if (PHP_MAJOR_VERSION >= 8 && (!$jit || $jit == "0" || $jit == "off")) {
            $result->addItem(new DiagnosticResultItem(
                DiagnosticResult::STATUS_INFORMATIONAL,
                Piwik::translate("DiagnosticsExtended_OpcacheCheckJIT")
            ));

        }
        return [$result];
    }


}
