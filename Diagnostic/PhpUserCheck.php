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
use Piwik\SettingsServer;
use Psr\Log\LoggerInterface;

class PhpUserCheck implements Diagnostic
{
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
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_PhpUserCheckLabel");
    }

    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        if (SettingsServer::isWindows()|| !function_exists("posix_getuid")) {
            return [];
        }
        if (posix_getuid() === 0) {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_PhpUserCheckWarning")
            )];
        }
        return [];
    }


}
