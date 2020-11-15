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
 * File: src/fcn/cron/openweather.php
 * Open Weather Updater
 */

/** @var mysqli $conn Global MYSQL Connection */
/**
 * @return array
 * @var object $config Global Config
 */
/**
 * @return array
 * @return array
 * @var object $data Weather Data
 * @var object $atlas Atlas Data
 */

if ($config->station->device === 0 && $config->station->primary_sensor === 0) {
    $data = array("station_id" => $config->upload->openweather->id, "dt" => time(), "temperature" => $data->tempC, "wind_speed" => round($data->windSpeedKMH / 3.6, 1), "wind_gust" => round($atlas->windGustKMH / 3.6, 1), "wind_deg" => $data->windDEG, "pressure" => $data->pressure_kPa * 10, "humidity" => $data->relH, "rain_1h" => $data->rainMM, "rain_24h" => $data->rainTotalMM_today,"APPID" => $config->upload->openweather->key);
} else {
    $data = array("station_id" => $config->upload->openweather->id, "dt" => time(), "temperature" => $data->tempC, "wind_speed" => round($data->windSpeedKMH / 3.6, 1), "wind_deg" => $data->windDEG, "pressure" => $data->pressure_kPa * 10, "humidity" => $data->relH, "rain_1h" => $data->rainMM, "rain_24h" => $data->rainTotalMM_today,"APPID" => $config->upload->openweather->key);
}
$openweatherQuery = json_encode($data);

$ch = curl_init($config->upload->openweather->url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $openweatherQuery);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$openweatherQueryResult = curl_exec($ch);
curl_close($ch);

// Save to DB
mysqli_query($conn,
    "INSERT INTO `openweather_updates` (`query`,`result`) VALUES ('$openweatherQuery', '$openweatherQueryResult')");
if ($config->debug->logging === true) {
    // Log it
    syslog(LOG_DEBUG, "(EXTERNAL)[OpenWeather]: Query = $openweatherQuery | Result = $openweatherQueryResult");
}
