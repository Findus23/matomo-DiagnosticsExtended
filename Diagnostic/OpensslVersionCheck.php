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
use Psr\Log\LoggerInterface;

class OpensslVersionCheck implements Diagnostic
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $label;

    /**
     * Use a rather old version as many security fixes are backported
     */
    const MINIMUM_VERSION = "1.0.2";
    const MINIMUM_VERSION_LETTER = "b";


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_OpensslVersionCheckLabel");
    }

    /**
     * @return DiagnosticResult
     */
    public function noOpenSSL()
    {
        return DiagnosticResult::singleResult(
            $this->label,
            DiagnosticResult::STATUS_INFORMATIONAL,
            Piwik::translate("DiagnosticsExtended_OpensslVersionCheckNoOpenssl")
        );
    }


    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        if (!extension_loaded("curl") || !extension_loaded('openssl')) {
            return [$this->noOpenSSL()];
        }
        $version = curl_version()["ssl_version"];
        if (strpos($version, "OpenSSL/") !== 0) {
            return [$this->noOpenSSL()];
        }
        $versionPart = substr($version, 8, 5);
        $letterPart = substr($version, 13, 1);
        if (
            version_compare($versionPart, self::MINIMUM_VERSION, "<")
            || (
                version_compare($versionPart, self::MINIMUM_VERSION, "=")
                && ord($letterPart) < ord(self::MINIMUM_VERSION_LETTER)
            )
        ) {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_OpensslVersionCheckOutdated", [$version])
            )];
        } else {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_INFORMATIONAL,
                Piwik::translate("DiagnosticsExtended_OpensslVersionCheckNotOutdated", [$version])
            )];
        }
    }
}
