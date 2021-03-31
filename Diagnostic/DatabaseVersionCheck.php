<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic;

use Piwik\Date;
use Piwik\Db;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Psr\Log\LoggerInterface;

class DatabaseVersionCheck implements Diagnostic
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
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_DatabaseVersionCheckLabel");
        $this->lazyCache = $lazyCache;
    }


    private function getDatabaseVersion()
    {
        $db = Db::get();
        return $db->getServerVersion();
    }


    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        $version = $this->getDatabaseVersion();
        $versionParts = explode(".", explode("-", $version)[0]);
        $minorVersion = $versionParts[0] . "." . $versionParts[1];
        $currentVersion = $minorVersion . "." . $versionParts[2];
        if (strpos(strtolower($version), "mariadb") !== false) {
            # check for MariaDB
            $cacheId = 'DiagnosticsExtended_MariaDBVersion_' . $minorVersion;

            $url = "https://endoflife.date/api/mariadb/$minorVersion.json";
            $timeout = self::SOCKET_TIMEOUT;
            try {
                $response = $this->lazyCache->fetch($cacheId);
                if (!$response) {
                    $response = Http::sendHttpRequest($url, $timeout);
                    $this->lazyCache->save($cacheId, $response, 60 * 60 * 24);
                }

                $versionInfo = json_decode($response, true);
                $latestVersion = $versionInfo["latest"];

                $results = new DiagnosticResult($this->label);
                if (version_compare($currentVersion, $latestVersion, ">=")) {
                    $results->addItem(new DiagnosticResultItem(
                        DiagnosticResult::STATUS_OK,
                        Piwik::translate("DiagnosticsExtended_PhpVersionCheckLatestVersion",
                            [$minorVersion])
                    ));
                } else {
                    $results->addItem(new DiagnosticResultItem(
                        DiagnosticResult::STATUS_WARNING,
                        Piwik::translate("DiagnosticsExtended_DatabaseVersionCheckMariaDBOutdated", [
                            $latestVersion, $version, $currentVersion
                        ])
                        . " "
                        . Piwik::translate("DiagnosticsExtended_BackportingDisclaimerMariaDB")
                        . "."
                    ));
                }
                $formattedDate = (Date::factory($versionInfo["eol"]))->getLocalized(Date::DATE_FORMAT_LONG);
                if (new \DateTime() > new \DateTime($versionInfo["eol"])) {
                    $results->addItem(new DiagnosticResultItem(
                        DiagnosticResult::STATUS_WARNING,
                        Piwik::translate("DiagnosticsExtended_DatabaseVersionCheckMariaDBEol", [
                            $currentVersion, $formattedDate
                        ])
                        . " "
                        . Piwik::translate("DiagnosticsExtended_BackportingDisclaimerMariaDB")
                        . "."
                    ));
                } else {
                    $results->addItem(new DiagnosticResultItem(
                        DiagnosticResult::STATUS_OK,
                        Piwik::translate("DiagnosticsExtended_DatabaseVersionCheckMariaDBNotEol", [
                            $minorVersion, $formattedDate
                        ])
                    ));
                }
                return [$results];


            } catch (\Exception $e) {
                return [DiagnosticResult::singleResult(
                    $this->label,
                    DiagnosticResult::STATUS_INFORMATIONAL,
                    "Matomo could not check if your Database version is up-to-date"
                )];
            }
        } else {
            # MySQL check
            # 5.7 is latest non-eol version
            if (((int)$versionParts[0] < 5) || ((int)$versionParts[0] == 5 && (int)$versionParts[1] >= 7)) {
                return [DiagnosticResult::singleResult(
                    $this->label,
                    DiagnosticResult::STATUS_WARNING,
                    "Your MySQL version might be not receiving security updates anymore 
                        (unless the distributor of your PHP binary is backporting security patches).
                        Please check if your MySQL version is still secure."
                )];
            } else {
                return [DiagnosticResult::singleResult(
                    $this->label,
                    DiagnosticResult::STATUS_OK,
                    "Your MySQL version is probably up-to-date 
                        (assuming you are using the latest patch version)."
                )];

            }
        }
    }

}
