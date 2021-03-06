<?php
/**
 * Acuparse - AcuRite Access/smartHUB and IP Camera Data Processing, Display, and Upload.
 * @copyright Copyright (C) 2015-2020 Maxwell Power
 * @author Maxwell Power <max@acuparse.com>
 * @link http://www.acuparse.com
 * @license AGPL-3.0+
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this code. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * File: src/fcn/cron/noChange.php
 * No Reading Changes
 */

/** @var mysqli $conn Global MYSQL Connection */
/**
 * @return array
 * @var object $config Global Config
 */

$lastUpdate = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT `timestamp` FROM `last_update`"));
// Check to see if the station is down

/*
 * NOTICE:
 * The Hub updates every min, so 5 min intervals is 5 updates missed and every 10 mins is 10 updates missed.
 * The Access updates every 5 mins, so 5 min intervals is only 1 update missed and every 10 mins is only 2 updates missed.
*/

if ($config->station->access_mac != 0) {
    if ($config->outage_alert->offline_for == '5 minutes') {
        $config->outage_alert->offline_for = '10 minutes';
    } elseif ($config->outage_alert->offline_for == '10 minutes') {
        $config->outage_alert->offline_for = '15 minutes';
    }
}

if ((strtotime($lastUpdate['timestamp']) < strtotime("-" . $config->outage_alert->offline_for))) {
    $outageAlert = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `last_sent`, `status` FROM `outage_alert`"));

    // Should a notification be sent?
    if (strtotime($outageAlert['last_sent']) < strtotime("-" . $config->outage_alert->interval)) {

        if ($config->outage_alert->enabled === true) {
            require_once(APP_BASE_PATH . '/fcn/mailer.php');
            $subject = 'Access/smartHUB offline! No Updates received.';
            $message = '<p><strong>Acuparse is not receiving updates from your Access/smartHUB.</strong><p>Check your internet connection.</p>';

            $sql = mysqli_query($conn, "SELECT `email` FROM `users` WHERE `admin` = '1'");
            while ($row = mysqli_fetch_assoc($sql)) {
                $admin_email[] = $row['email'];
            }

            // Mail it
            foreach ($admin_email as $to) {
                mailer($to, $subject, $message);
            }
            // Log it
            syslog(LOG_ERR,
                "(SYSTEM)[ERROR]: OFFLINE: not receiving data from the Access/smartHUB. Email sent to admin.");
            // Update the time the email was sent
            $lastSent = date("Y-m-d H:i:s");
            mysqli_query($conn, "UPDATE `outage_alert` SET `last_sent` = '$lastSent', `status` = '0'");

        } else {
            // Log it
            syslog(LOG_ERR, "(SYSTEM)[ERROR]: OFFLINE: not receiving data from the Access/smartHUB.");
            // Update the status
            mysqli_query($conn, "UPDATE `outage_alert` SET `status` = '0'");
        }
    } else {
        // Log it
        syslog(LOG_ERR, "(SYSTEM)[ERROR]: OFFLINE: Too soon to send another notification.");
    }
} // Not offline long enough,
else {
    // Log it
    syslog(LOG_INFO, "(SYSTEM)[INFO]: No update to send. There is no new data to send or station is offline.");
}
