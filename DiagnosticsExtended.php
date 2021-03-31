<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DiagnosticsExtended;

use Piwik\Notification;
use Piwik\Piwik;

class DiagnosticsExtended extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Request.dispatch' => "addNotification"
        ];
    }

    public function addNotification(&$module, &$action, &$parameters)
    {
        if ($module == "Installation" && $action == "systemCheckPage") {
            $id = 'DiagnosticsExtended_Help';

            $notification = new Notification(Piwik::translate("DiagnosticsExtended_NotificationText",
                [
                    '<a href="https://forum.matomo.org/" target="_blank" rel="noopener">',
                    '</a>',
                    '<a href="https://github.com/Findus23/matomo-DiagnosticsExtended/issues" target="_blank" rel="noopener">',
                    '</a>'
                ]
            ));
            $notification->raw = true;
            $notification->title = Piwik::translate('DiagnosticsExtended_NotificationTitle');
            $notification->context = Notification::CONTEXT_INFO;
            \Piwik\Notification\Manager::notify($id, $notification);
        }
    }



}
