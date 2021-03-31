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
use Piwik\SettingsServer;
use Psr\Log\LoggerInterface;

class PhpUserCheck implements Diagnostic
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        if (SettingsServer::isWindows()) {
            return [];
        }
        if (posix_getuid() === 0) {
            return [DiagnosticResult::singleResult(
                "🧪 php running as root",
                DiagnosticResult::STATUS_WARNING,
                "PHP seems to be running as root. Unless you are using Matomo inside a docker container
                you should check your setup."
            )];
        }
        return [];
    }


}
