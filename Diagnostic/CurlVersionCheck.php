<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic;

use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\SettingsPiwik;
use Psr\Log\LoggerInterface;

class CurlVersionCheck implements Diagnostic
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $label;

    const SOCKET_TIMEOUT = 2;
    const CACHEID = "DiagnosticsExtended_CurlVulnerabilities";

    /**
     * @var \Matomo\Cache\Lazy
     */
    private $lazyCache;


    public function __construct(LoggerInterface $logger, \Matomo\Cache\Lazy $lazyCache)
    {
        $this->logger = $logger;
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_CurlVersionCheckLabel");
        $this->lazyCache = $lazyCache;
    }


    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        if (!extension_loaded('curl')) {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_INFORMATIONAL,
                Piwik::translate("DiagnosticsExtended_CurlVersionCheckNoCurl")
            )];
        }
        $version = curl_version()["version"];

        $url = "https://curl.se/docs/vuln.pm";
        $timeout = self::SOCKET_TIMEOUT;
        try {
            if (!SettingsPiwik::isInternetEnabled()) {
                throw new \Exception("internet is disabled");
            }
            $response = $this->lazyCache->fetch(self::CACHEID);
            if (!$response) {
                $response = Http::sendHttpRequest($url, $timeout);
                $this->lazyCache->save(self::CACHEID, $response, 60 * 60 * 24 * 7);
            }
            $vulns = [];
            foreach (explode("\n", $response) as $line) {
                $line = trim($line);
                if (strpos($line, "#") === 0 || strpos($line, "@") === 0 || strpos($line, ")") === 0) {
                    continue;
                }
                $line = str_replace('"', "", $line);
                $cols = explode("|", $line);
                $startVersion = $cols[1];
                $endVersion = $cols[2];
                $URL = htmlspecialchars($cols[0], ENT_QUOTES, 'UTF-8');
                $CVE = htmlspecialchars($cols[4], ENT_QUOTES, 'UTF-8');
                if (
                    version_compare($version, $startVersion, ">=") &&
                    version_compare($version, $endVersion, "<=")
                ) {
                    $vulns[] = "<a target='_blank' rel='noopener' href='https://curl.se/docs/$URL'>$CVE</a>";
                }
            }
            if (count($vulns) > 0) {
                return [DiagnosticResult::singleResult(
                    $this->label,
                    DiagnosticResult::STATUS_ERROR,
                    Piwik::translate("DiagnosticsExtended_CurlVersionCheckVulnerable",[$version])
                    . " "
                    . join(", ", $vulns)
                )];
            } else {
                return [DiagnosticResult::singleResult(
                    $this->label,
                    DiagnosticResult::STATUS_OK,
                    Piwik::translate("DiagnosticsExtended_CurlVersionCheckUpToDate", [$version])
                )];
            }
        } catch (\Exception $e) {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_INFORMATIONAL,
                Piwik::translate("DiagnosticsExtended_CurlVersionCheckFailed")
            )];
        }


    }
}
