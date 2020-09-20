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
 * File: src/fcn/cron/weatherunderground.php
 * Weather Underground Updater
 */

/** @var mysqli $conn Global MYSQL Connection */
/**
 * @return array
 * @var object $config Global Config
 */
/**
 * @return array
 * @var object $data Weather Data
 * @return array
 * @var object $atlas Atlas Data
 * @return array
 * @var object $appInfo Global Application Info
 * @var string $utcDate Atlas Data
 */

$genericQueryUrl = $config->upload->generic->url . '?ID=' . $config->upload->generic->id . '&PASSWORD=' . $config->upload->generic->password;
$genericQuery = '&dateutc=' . $utcDate . '&tempf=' . $data->tempF . '&winddir=' . $data->windDEG . '&windspeedmph=' . $data->windSpeedMPH . '&baromin=' . $data->pressure_inHg . '&humidity=' . $data->relH . '&dewptf=' . $data->dewptF . '&rainin=' . $data->rainIN . '&dailyrainin=' . $data->rainTotalIN_today;
if ($config->station->device === 0 && $config->station->primary_sensor === 0) {
    $genericQuery = $genericQuery . '&windspdmph_avg2m=' . $atlas->windAvgMPH . '&windgustmph' . $atlas->windGust . '&windgustdir' . $atlas->windGustDEG . '&UV=' . $atlas->uvIndex;
}
$genericQueryStatic = '&softwaretype=' . ucfirst($appInfo->name) . '&action=updateraw';
$genericQueryResult = file_get_contents(htmlentities($genericQueryUrl . $genericQuery . $genericQueryStatic));
// Save to DB
mysqli_query($conn,
    "INSERT INTO `generic_updates` (`query`,`result`) VALUES ('$genericQuery', '$genericQueryResult')");
if ($config->debug->logging === true) {
    // Log it
    syslog(LOG_DEBUG, "(EXTERNAL)[GENERIC]: Query = $genericQuery | Result = $genericQueryResult");
}
