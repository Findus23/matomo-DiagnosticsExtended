<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended\Diagnostic;

use Piwik\Http;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\SettingsPiwik;
use Piwik\Tracker\TrackerCodeGenerator;
use Psr\Log\LoggerInterface;

class GzipMatomoJsCheck implements Diagnostic
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


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->label = "matomo.js gzip";
    }


    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        $matomoUrl = SettingsPiwik::getPiwikUrl();
        $generator = new TrackerCodeGenerator();
        $matomoJs = $generator->getJsTrackerEndpoint();
        $checkURL = "$matomoUrl$matomoJs"; # something like https://example.com/matomo.js
        $timeout = self::SOCKET_TIMEOUT;
        try {
            $response = Http::sendHttpRequest($checkURL, $timeout, $userAgent = null,
                $destinationPath = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $byteRange = false,
                $getExtendedInfo = true);
            $status = $response["status"];
            $headers = $response["headers"];
            $data = $response["data"];
            if ($status != 200 || strpos($data, "c80d50af7d3db9be66a4d0a86db0286e4fd33292") === false) {
                return [DiagnosticResult::singleResult(
                    $this->label,
                    DiagnosticResult::STATUS_INFORMATIONAL,
                    "It seems like matomo.js can't be fetched properly"
                )];
            }
            $results = new DiagnosticResult($this->label);
            $contentType = $headers["content-type"];
            if ($contentType !== "application/javascript") {
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_WARNING,
                    "matomo.js should be delivered with an 'application/javascript' Content-Type. You are using '$contentType'."
                ));

            }
            $contentEncoding = $headers["content-encoding"];
            if ($contentEncoding === "gzip") {
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_OK,
                    "matomo.js is delivered gzipped."
                ));

            } else {
                $results->addItem(new DiagnosticResultItem(
                    DiagnosticResult::STATUS_WARNING,
                    "matomo.js is not delivered gzipped. 
                    You might want to set up gzip for .js files as it can reduce the size of the file by up to 60 %."
                ));
            }
            return [$results];

        } catch (\Exception $e) {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_INFORMATIONAL,
                "Matomo could not check if your matomo.js can be fetched properly"
            )];

        }
    }
}
