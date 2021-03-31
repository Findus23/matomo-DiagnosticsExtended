<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic;

use Piwik\Date;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Psr\Log\LoggerInterface;

class PhpVersionCheck implements Diagnostic
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
    /**
     * @var \Matomo\Cache\Lazy
     */
    private $lazyCache;


    public function __construct(LoggerInterface $logger, \Matomo\Cache\Lazy $lazyCache)
    {
        $this->logger = $logger;
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_PhpVersionCheckLabel");
        $this->lazyCache = $lazyCache;
    }

    /**
     * from
     * https://www.php.net/supported-versions
     * and
     * https://www.php.net/eol.php
     * @var string[]
     */
    private $eolDates = [
        "7.2" => "2020-11-30",
        "7.3" => "2021-12-06",
        "7.4" => "2022-11-28",
        "8.0" => "2023-11-26"
    ];


    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        $minorVersion = PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;
        $currentVersion = $minorVersion . "." . PHP_RELEASE_VERSION;
        $cacheId = 'DiagnosticsExtended_PhpVersion_' . $minorVersion;

        $url = "https://php.net/releases/?json=1&version=" . $minorVersion;
        $timeout = self::SOCKET_TIMEOUT;
        try {
            $response = $this->lazyCache->fetch($cacheId);
            if (!$response) {
                $response = Http::sendHttpRequest($url, $timeout);
                $this->lazyCache->save($cacheId, $response, 60 * 60 * 24);
            }
            $versionInfo = json_decode($response, true);
            if (empty($versionInfo["version"])) {
                return [$this->testCouldNotRunResult()];
            }
            $latestVersion = $versionInfo["version"];
            $results = new DiagnosticResult($this->label);
            if (version_compare($currentVersion, $latestVersion, ">=")) {
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_OK,
                    Piwik::translate("DiagnosticsExtended_PhpVersionCheckLatestVersion", [$minorVersion])
                ));
            } else {
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_WARNING,
                    Piwik::translate("DiagnosticsExtended_PhpVersionCheckOutdated", [
                        $latestVersion, $currentVersion
                    ])
                    . " "
                    . Piwik::translate("DiagnosticsExtended_BackportingDisclaimerPHP")
                    . "."
                ));
            }
            if (empty($this->eolDates[$minorVersion])) {
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_INFORMATIONAL,
                    Piwik::translate("DiagnosticsExtended_PhpVersionCheckNoInformation", [
                        $latestVersion, $currentVersion
                    ])
                ));

            } elseif (new \DateTime() > new \DateTime($this->eolDates[$minorVersion])) {
                $formattedDate = (Date::factory($this->eolDates[$minorVersion]))->getLocalized(Date::DATE_FORMAT_LONG);
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_WARNING,
                    Piwik::translate("DiagnosticsExtended_PhpVersionCheckEol", [
                        $currentVersion, $formattedDate
                    ])
                    . " "
                    . Piwik::translate("DiagnosticsExtended_BackportingDisclaimerPHP")
                    . "."
                ));
            } else {
                $formattedDate = (Date::factory($this->eolDates[$minorVersion]))->getLocalized(Date::DATE_FORMAT_LONG);
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_OK,
                    Piwik::translate("DiagnosticsExtended_PhpVersionCheckEol", [
                        $minorVersion, $formattedDate
                    ])
                ));
            }
        } catch (\Exception $e) {
            $this->logger->warning($e);
            return [$this->testCouldNotRunResult()];
        }
        return [$results];
    }

    private function testCouldNotRunResult()
    {
        return DiagnosticResult::singleResult(
            $this->label,
            DiagnosticResult::STATUS_INFORMATIONAL,
            Piwik::translate("DiagnosticsExtended_PhpVersionCheckNotWorking")
        );
    }
}
