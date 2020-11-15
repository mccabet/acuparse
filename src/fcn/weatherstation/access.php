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
 * File: src/fcn/updates/access.php
 * Processes an update from an Access
 */

/** @var mysqli $conn Global MYSQL Connection */
/**
 * @return array
 * @var object $config Global Config
 */

/** @var string $myacuriteQuery */

require(APP_BASE_PATH . '/fcn/weather/lightningUpdate.php');

// Set the device ID
$device = 'A';

// Process UTC timestamp
$timestamp = (string)mysqli_real_escape_string($conn,
    filter_input(INPUT_GET, 'dateutc', FILTER_SANITIZE_STRING));
$timestamp = str_replace('T', ' ', $timestamp);
$timestamp = strtotime($timestamp . ' UTC');
$timestamp = date("Y-m-d H:i:s", $timestamp);
$date = date('Y-m-d', $timestamp);

// Build update data
$postData = http_build_query($_POST);
$opts = array(
    'http' =>
        array(
            'method' => 'POST',
            'header' => 'User-Agent:' . $_SERVER['HTTP_USER_AGENT'],
            'content' => $postData
        ),
    'ssl' =>
        array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        )
);
$context = stream_context_create($opts);

// Process 5-in-1 Update
if ($_GET['mt'] === '5N1') {

    // Set the source
    $source = '5';

    if ($_GET['sensor'] === $config->station->sensor_5n1 && $config->station->primary_sensor === 1) {

        //Barometer
        $baromin = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'baromin', FILTER_SANITIZE_STRING));
        if ($config->station->baro_offset !== 0) {
            $baromin = $baromin + $config->station->baro_offset;
        }
        // Wind Speed
        $windSpeedMPH = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windspeedmph', FILTER_SANITIZE_STRING));
        $windGustMPH = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windgustmph', FILTER_SANITIZE_STRING));
        $windSpeedAvgMPH = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windspeedavgmph', FILTER_SANITIZE_STRING));

        // Wind Direction
        $windDirection = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'winddir', FILTER_SANITIZE_STRING));
        $windGustDirection = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windgustdir', FILTER_SANITIZE_STRING));

        // Rainfall
        $rainIN = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'rainin', FILTER_SANITIZE_STRING));
        $dailyRainIN = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'dailyrainin', FILTER_SANITIZE_STRING));

        // Temperature
        $tempF = (float)mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'tempf', FILTER_SANITIZE_STRING));
        $heatIndex = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'heatindex', FILTER_SANITIZE_STRING));
        $feelsLike = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'feelslike', FILTER_SANITIZE_STRING));
        $windChill = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windchill', FILTER_SANITIZE_STRING));
        $dewptF = (float)mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'dewptf', FILTER_SANITIZE_STRING));

        // Humidity
        $humidity = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'humidity', FILTER_SANITIZE_STRING));

        $dewptF = (float)mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'dewptf', FILTER_SANITIZE_STRING));

        //Other
        $battery = (string)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'sensorbattery', FILTER_SANITIZE_STRING));
        $rssi = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'rssi', FILTER_SANITIZE_STRING));
        $batteryAccess = (string)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'hubbattery', FILTER_SANITIZE_STRING));

        // Insert 5N1 Readings into DB
        $sql = "INSERT INTO `windspeed` (`speedMPH`, `gustMPH`, `averageMPH`, `timestamp`, `device`, `source`) VALUES ('$windSpeedMPH', '$windGustMPH', '$windSpeedAvgMPH', '$timestamp', '$device', '$source');
            INSERT INTO `temperature` (`tempF`, `heatindex`, `feelslike`, `windchill`, `dewptf`, `timestamp`, `device`, `source`) VALUES ('$tempF', '$heatIndex', '$feelsLike', '$windChill', '$dewptF', '$timestamp', '$device', '$source');
            INSERT INTO `winddirection` (`degrees`, `gust`, `timestamp`, `device`, `source`) VALUES ('$windDirection', '$windGustDirection', '$timestamp', '$device', '$source');
            INSERT INTO `humidity` (`relH`, `timestamp`, `device`, `source`) VALUES ('$humidity', '$timestamp', '$device', '$source');
            UPDATE `rainfall` SET `rainin`='$rainIN', `last_update`='$timestamp', `device`='$device', `source`='$source';
            INSERT INTO `dailyrain` (`dailyrainin`, `date`, `last_update`, `device`, `source`) VALUES ('$dailyRainIN', '$date', '$timestamp', '$device', '$source') ON DUPLICATE KEY UPDATE `dailyrainin`='$dailyRainIN', `last_update`='$timestamp', `device`='$device', `source`='$source';
            INSERT INTO `pressure` (`inhg`, `timestamp`, `device`, `source`) VALUES ('$baromin', '$timestamp', '$device', '$source');
            UPDATE `access_status` SET `battery`='$batteryAccess',`last_update`='$timestamp';
            UPDATE `5n1_status` SET `battery`='$battery', `rssi`='$rssi', `last_update`='$timestamp' WHERE `device`='access';";
        $result = mysqli_multi_query($conn, $sql) or syslog(LOG_ERR, "(ACCESS)[5N1][SQL ERROR]:" . mysqli_error($conn));
        while (mysqli_next_result($conn)) {
            null;
        }

        // Log it
        if ($config->debug->logging === true) {
            syslog(LOG_DEBUG,
                "(ACCESS)[5N1]: Pressure = $baromin");
            syslog(LOG_DEBUG,
                "(ACCESS)[5N1]: TempF = $tempF | relH = $humidity | Wind = $windDirection @ $windSpeedMPH | Rain = $rainIN | DailyRain = $dailyRainIN");
            syslog(LOG_DEBUG, "(ACCESS)[5N1]: Battery = $battery | Signal = $rssi");
            syslog(LOG_DEBUG, "(ACCESS)[SYS]: Battery = $batteryAccess");
        }

        // Update the time the data was received
        last_updated_at();
    }
} //Done 5N1

// Process Atlas Update
elseif ($_GET['mt'] === 'Atlas') {
    $source = 'A';

    if ($_GET['sensor'] === $config->station->sensor_atlas && $config->station->primary_sensor === 0) {

        //Barometer
        $baromin = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'baromin', FILTER_SANITIZE_STRING));
        if ($config->station->baro_offset !== 0) {
            $baromin = $baromin + $config->station->baro_offset;
        }
        // Wind Speed
        $windSpeedMPH = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windspeedmph', FILTER_SANITIZE_STRING));
        $windSpeedAvgMPH = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windspeedavgmph', FILTER_SANITIZE_STRING));
        $windGustMPH = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windgustmph', FILTER_SANITIZE_STRING));

        // Wind Direction
        $windDirection = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'winddir', FILTER_SANITIZE_STRING));
        $windGustDirection = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windgustdir', FILTER_SANITIZE_STRING));

        // Rainfall
        $rainIN = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'rainin', FILTER_SANITIZE_STRING));
        $dailyRainIN = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'dailyrainin', FILTER_SANITIZE_STRING));

        // Temperature
        $tempF = (float)mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'tempf', FILTER_SANITIZE_STRING));

        // Humidity
        $humidity = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'humidity', FILTER_SANITIZE_STRING));

        // Indexes
        $heatIndex = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'heatindex', FILTER_SANITIZE_STRING));
        $feelsLike = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'feelslike', FILTER_SANITIZE_STRING));
        $windChill = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'windchill', FILTER_SANITIZE_STRING));
        $dewptF = (float)mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'dewptf', FILTER_SANITIZE_STRING));

        // Atlas Specific Sensors
        $uvindex = (float)mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'uvindex', FILTER_SANITIZE_STRING));
        $lightintensity = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'lightintensity', FILTER_SANITIZE_STRING));
        $measured_light_seconds = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'measured_light_seconds', FILTER_SANITIZE_STRING));

        // Lightning
        $strikecount = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'strikecount', FILTER_SANITIZE_STRING));
        $interference = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'interference', FILTER_SANITIZE_STRING));
        $last_strike_distance = (float)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'last_strike_distance', FILTER_SANITIZE_STRING));
        // Process Last Strike timestamp
        $last_strike_ts = (string)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'last_strike_ts', FILTER_SANITIZE_STRING));
        $last_strike_ts = str_replace('T', ' ', $last_strike_ts);
        $last_strike_ts = strtotime($last_strike_ts . ' UTC');
        $last_strike_ts = date("Y-m-d H:i:s", $last_strike_ts);

        //Other
        $battery = (string)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'sensorbattery', FILTER_SANITIZE_STRING));
        $rssi = (int)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'rssi', FILTER_SANITIZE_STRING));
        $batteryAccess = (string)mysqli_real_escape_string($conn,
            filter_input(INPUT_GET, 'hubbattery', FILTER_SANITIZE_STRING));

        // Insert Atlas readings into DB
        $sql = "INSERT INTO `windspeed` (`speedMPH`, `gustMPH`, `averageMPH`, `timestamp`, `device`, `source`) VALUES ('$windSpeedMPH', '$windGustMPH', '$windSpeedAvgMPH', '$timestamp', '$device', '$source');
            INSERT INTO `temperature` (`tempF`, `heatindex`, `feelslike`, `windchill`, `dewptf`, `timestamp`, `device`, `source`) VALUES ('$tempF', '$heatIndex', '$feelsLike', '$windChill', '$dewptF', '$timestamp', '$device', '$source');
            INSERT INTO `winddirection` (`degrees`, `gust`, `timestamp`, `device`, `source`) VALUES ('$windDirection', '$windGustDirection', '$timestamp', '$device', '$source');
            INSERT INTO `humidity` (`relH`, `timestamp`, `device`, `source`) VALUES ('$humidity', '$timestamp', '$device', '$source');
            UPDATE `rainfall` SET `rainin`='$rainIN', `last_update`='$timestamp', `device`='$device', `source`='$source';
            INSERT INTO `dailyrain` (`dailyrainin`, `date`, `last_update`, `device`, `source`) VALUES ('$dailyRainIN', '$date', '$timestamp', '$device', '$source') ON DUPLICATE KEY UPDATE `dailyrainin`='$dailyRainIN', `last_update`='$timestamp', `device`='$device', `source`='$source';
            INSERT INTO `uvindex` (`uvindex`, `timestamp`) VALUES('$uvindex', '$timestamp');
            INSERT INTO `light` (`lightintensity`, `measured_light_seconds`, `timestamp`) VALUES('$lightintensity', '$measured_light_seconds', '$timestamp');
            INSERT INTO `pressure` (`inhg`, `timestamp`, `device`, `source`) VALUES ('$baromin', '$timestamp', '$device', '$source');
            UPDATE `access_status` SET `battery`='$batteryAccess',`last_update`='$timestamp';
            UPDATE `atlas_status` SET `battery`='$battery', `rssi`='$rssi', `last_update`='$timestamp';";
        $result = mysqli_multi_query($conn, $sql) or syslog(LOG_ERR, "(ACCESS)[ATLAS][SQL ERROR]:" . mysqli_error($conn));
        while (mysqli_next_result($conn)) {
            null;
        }

        // Lightning

        updateLightning($strikecount, $interference, $last_strike_ts, $last_strike_distance, 'A');

        // Log it
        if ($config->debug->logging === true) {
            syslog(LOG_DEBUG,
                "(ACCESS)[SYS]: Pressure = $baromin");
            syslog(LOG_DEBUG,
                "(ACCESS)[ATLAS]: TempF = $tempF | relH = $humidity | Windspeed = $windSpeedMPH | Wind = $windDirection @ $windSpeedMPH | Rain = $rainIN | DailyRain = $dailyRainIN | UV = $uvindex | Light = $lightintensity / $measured_light_seconds");
            syslog(LOG_DEBUG, "(ACCESS)[ATLAS]: Battery = $battery | Signal = $rssi");
            syslog(LOG_DEBUG, "(ACCESS)[SYS]: Battery = $batteryAccess");
        }

        // Update the time the data was received
        last_updated_at();
    }
} // Done Atlas

// Process Tower Sensors
elseif ($config->station->towers === true) {
    if ($_GET['mt'] === 'tower' || $_GET['mt'] === 'ProOut' || $_GET['mt'] === 'ProIn' || $_GET['mt'] === 'light') {

        // Tower ID
        $towerID = mysqli_real_escape_string($conn, filter_input(INPUT_GET, 'sensor', FILTER_SANITIZE_NUMBER_INT));

        // Check if this tower exists
        $sql = "SELECT * FROM `towers` WHERE `sensor` = '$towerID';";
        $count = mysqli_num_rows(mysqli_query($conn, $sql)) or syslog(LOG_ERR, "(ACCESS)[TOWER][WARNING]: Tower Does Not Exist!");
        if ($count === 1) {
            $result = mysqli_fetch_assoc(mysqli_query($conn, $sql)) or syslog(LOG_ERR, "(ACCESS)[TOWER][SQL ERROR]:" . mysqli_error($conn));
            $towerName = $result['name'];

            // ProIn Specific Variables
            if ($_GET['mt'] === 'ProIn') {
                $tempF = (float)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'indoortempf', FILTER_SANITIZE_STRING));
                $humidity = (int)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'indoorhumidity', FILTER_SANITIZE_STRING));
            } else {
                // Temperature
                $tempF = (float)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'tempf', FILTER_SANITIZE_STRING));

                // Humidity
                $humidity = (int)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'humidity', FILTER_SANITIZE_STRING));
            }

            // Lightning
            if ($_GET['mt'] === 'light') {
                $strikecount = (float)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'strikecount', FILTER_SANITIZE_STRING));
                $interference = (int)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'interference', FILTER_SANITIZE_STRING));
                $last_strike_distance = (float)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'last_strike_distance', FILTER_SANITIZE_STRING));
                // Process Last Strike timestamp
                $last_strike_ts = (string)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'last_strike_ts', FILTER_SANITIZE_STRING));
                $last_strike_ts = str_replace('T', ' ', $last_strike_ts);
                $last_strike_ts = strtotime($last_strike_ts . ' UTC');
                $last_strike_ts = date("Y-m-d H:i:s", $last_strike_ts);
            }

            //Other
            $battery = (string)mysqli_real_escape_string($conn,
                filter_input(INPUT_GET, 'sensorbattery', FILTER_SANITIZE_STRING));
            $rssi = (int)mysqli_real_escape_string($conn,
                filter_input(INPUT_GET, 'rssi', FILTER_SANITIZE_STRING));

            // Insert into DB
            $sql = "INSERT INTO `tower_data` (`tempF`, `relH`, `sensor`, `battery`, `rssi`, `timestamp`, `device`) VALUES ('$tempF', '$humidity', '$towerID', '$battery', '$rssi', '$timestamp', '$device');";
            $result = mysqli_query($conn, $sql) or syslog(LOG_ERR, "(ACCESS)[TOWER][SQL ERROR]:" . mysqli_error($conn));

            // Check if this is the upload tower and save the baro. reading
            if ($config->upload->sensor->id === $towerID) {
                //Barometer
                $baromin = (float)mysqli_real_escape_string($conn,
                    filter_input(INPUT_GET, 'baromin', FILTER_SANITIZE_STRING));
                if ($config->station->baro_offset !== 0) {
                    $source = 'T';
                    $baromin = $baromin + $config->station->baro_offset;
                }

                // Insert into DB
                $sql = "INSERT INTO `pressure` (`inhg`, `timestamp`, `device`, `source`) VALUES ('$baromin', '$timestamp', '$device', '$source');";
                $result = mysqli_query($conn, $sql) or syslog(LOG_ERR, "(ACCESS)[TOWER]{PRESSURE}[SQL ERROR]:" . mysqli_error($conn));

                // Log it
                if ($config->debug->logging === true) {
                    syslog(LOG_DEBUG,
                        "(ACCESS)[SYS]: Pressure = $baromin");
                }
            }

            // Lightning
            if ($_GET['mt'] === 'light') {
                updateLightning($strikecount, $interference, $last_strike_ts, $last_strike_distance, 'T');
            }

            // Log it
            if ($config->debug->logging === true) {
                syslog(LOG_DEBUG, "(ACCESS)[TOWER][$towerName]: tempF = $tempF | relH = $humidity");
                syslog(LOG_DEBUG, "(ACCESS)[TOWER][$towerName]: Battery = $battery | Signal = $rssi");
            }
        } // This tower has not been added
        else {
            syslog(LOG_ERR, "(ACCESS)[TOWER][ERROR]: Unknown ID: $towerID . Raw = $myacuriteQuery");
            goto upload_unknown;
        }
    }
} // Done Towers

// This sensor is not added
else {
    $sensor = $_GET['sensor'];
    if ($_GET['mt'] === 'tower' || $_GET['mt'] === 'ProOut' || $_GET['mt'] === 'ProIn' || $_GET['mt'] === 'light') {
        syslog(LOG_ERR,
            "(ACCESS)[TOWER][ERROR]: Towers not enabled - Tower ID $sensor . Raw = $myacuriteQuery");
    } elseif ($_GET['mt'] === '5N1') {
        syslog(LOG_ERR, "(ACCESS)[5N1][ERROR]: Unknown Sensor ID $sensor . Raw = $myacuriteQuery");
    } elseif ($_GET['mt'] === 'Atlas') {
        syslog(LOG_ERR, "(ACCESS)[ATLAS][ERROR]: Unknown Sensor ID $sensor . Raw = $myacuriteQuery");
    } else {
        syslog(LOG_ERR, "(ACCESS)[ERROR]: Unknown Sensor $sensor . Raw = $myacuriteQuery");
    }

    upload_unknown:
    // Upload unknown sensor
    if ($config->upload->myacurite->pass_unknown === true) {
        goto myacurite_upload;
    } else {
        exit();
    }
}

// Finish Update

// Send data to debug server
if ($config->debug->server->enabled === true) {
    file_get_contents('https://' . $config->debug->server->url . '/weatherstation/updateweatherstation?&' . $myacuriteQuery,
        false, $context);
}

myacurite_upload:
// Forward the raw data to MyAcurite
if ($config->upload->myacurite->access_enabled === true) {
    $myacurite = file_get_contents($config->upload->myacurite->access_url . '/weatherstation/updateweatherstation?&' . $myacuriteQuery,
        false, $context);

    // Make sure data was sent
    if (!$myacurite) {
        goto myacurite_upload_disabled;
    } else {

        // Log the raw data
        if ($config->debug->logging === true) {
            syslog(LOG_DEBUG, "(ACCESS)[MyAcuRite]: Query = $myacuriteQuery | Response = $myacurite");
        }

        // Output the response to the Access
        echo $myacurite;
    }
} // MyAcurite is disabled
else {
    myacurite_upload_disabled:
    // Output the expected response to the Access
    $accessTimezoneOffset = date('P');
    $myacurite = '{"timezone":"' . $accessTimezoneOffset . '"}';
    // Log the raw data
    if ($config->debug->logging === true) {
        syslog(LOG_DEBUG, "(ACCESS)[MyAcuRite]: Query = $myacuriteQuery | Response = $myacurite");
    }
    echo $myacurite;
}
