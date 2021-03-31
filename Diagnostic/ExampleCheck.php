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
use Psr\Log\LoggerInterface;

class ExampleCheck implements Diagnostic
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = new DiagnosticResult("🧪 label");
        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, "error"));
        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, "warning"));
        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_OK, "okay"));
        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, "info"));
        return array($result);
    }


}
