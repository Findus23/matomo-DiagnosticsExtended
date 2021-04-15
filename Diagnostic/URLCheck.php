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
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;
use Piwik\SettingsPiwik;
use Psr\Log\LoggerInterface;

class URLCheck implements Diagnostic
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    const SOCKET_TIMEOUT = 2;
    /**
     * @var string
     */
    private $matomoURL;
    /**
     * @var boolean
     */
    private $criticalIssue;
    /**
     * @var string
     */
    private $label;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->matomoURL = SettingsPiwik::getPiwikUrl();
        $this->criticalIssue = false;
        $this->label = "🧪 " . Piwik::translate("DiagnosticsExtended_URLCheckLabel");

    }

    public function execute()
    {
        if (!SettingsPiwik::isInternetEnabled()) {
            return [DiagnosticResult::singleResult(
                $this->label,
                DiagnosticResult::STATUS_INFORMATIONAL,
                Piwik::translate("DiagnosticsExtended_URLCheckSkipped")
            )]
        }
        //TODO: don't check if running in development mode
        $result = new DiagnosticResult($this->label);
        $result->addItem($this->checkConfigIni());
        $result->addItem($this->checkRequestNotAllowed(
            ".git/info/exclude",
            "Lines that start"
        ));
        $result->addItem($this->checkRequestNotAllowed(
            "tmp/cache/token.php",
            "?php exit"
        ));
        $result->addItem($this->checkRequestNotAllowed(
            "cache/tracker/matomocache_general.php",
            "unserialize"
        ));
        $result->addItem($this->checkRequestNotAllowed(
            "lang/en.json",
            "12HourClock",
            false
        ));

        if ($this->criticalIssue) {
            $result->setLongErrorMessage(Piwik::translate("DiagnosticsExtended_URLCheckLongErrorMessage", ["<a href='https://github.com/matomo-org/matomo-nginx/' target='_blank' rel='noopener'>", "</a>"])
            );
        }
        return array($result);
    }

    /**
     * @return DiagnosticResultItem
     */
    protected function checkConfigIni()
    {
        $relativeUrl = "config/config.ini.php";
        list($status, $headers, $data) = $this->makeHTTPReququest($relativeUrl);
        if ($this->contains($data, "salt")) {
            return $this->isPublicError($relativeUrl, true);
        }
        if ($this->contains($data, ";")) {
            return new DiagnosticResultItem(
                DiagnosticResult::STATUS_WARNING,
                Piwik::translate("DiagnosticsExtended_URLCheckConfigIni", ["<code>$relativeUrl</code>"])
            );
        }
        else {
            return new DiagnosticResultItem(
                DiagnosticResult::STATUS_OK,
                Piwik::translate("DiagnosticsExtended_URLCheckOk", ["<code>$relativeUrl</code>"])
            );
        }
    }

    protected function checkRequestNotAllowed($relativeUrl, $content, $critical = true): DiagnosticResultItem
    {
        list($status, $headers, $data) = $this->makeHTTPReququest($relativeUrl);
        if (strpos($data, $content) !== false) {
            return $this->isPublicError($relativeUrl, $critical);
        }

        return new DiagnosticResultItem(DiagnosticResult::STATUS_OK, Piwik::translate("DiagnosticsExtended_URLCheckOk", ["<code>$relativeUrl</code>"]));
    }

    protected function isPublicError($relativeUrl, $critical): DiagnosticResultItem
    {
        if ($critical) {
            $this->criticalIssue = true;
        }
        return new DiagnosticResultItem(
            $critical ? DiagnosticResult::STATUS_ERROR : DiagnosticResult::STATUS_WARNING,
            Piwik::translate("DiagnosticsExtended_URLCheckError", ["<code>$relativeUrl</code>"])
        );
    }

    protected function makeHTTPReququest($relativeUrl)
    {
        $response = Http::sendHttpRequest($this->matomoURL . $relativeUrl, self::SOCKET_TIMEOUT, $userAgent = null,
            $destinationPath = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $byteRange = false,
            $getExtendedInfo = true);
        $status = $response["status"];
        $headers = $response["headers"];
        $data = $response["data"];
        return [$status, $headers, $data];
    }

    protected function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }


}
