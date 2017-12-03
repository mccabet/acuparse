<?php
/**
 * Acuparse - AcuRite®‎ smartHUB and IP Camera Data Processing, Display, and Upload.
 * @copyright Copyright (C) 2015-2017 Maxwell Power
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
 * File: src/pub/admin/install/updates.php
 * Site Update Tasks
 */

switch ($config->version->app){
    // Update from 2.1.0
    case '2.1.0':
    // Update from 2.1.1
    case '2.1.1':
    // Update from 2.1.2
    case '2.1.2':
    // Update from 2.1.3
    case '2.1.3':
    // Update from 2.1.4
    case '2.1.4':
    // Update from 2.1.5
    case '2.1.5':
    // Update from 2.1.6
    case '2.1.6':
}
//$config->version->schema = '2.1';
$config->version->app = '2.1.7';
$notes = 'Weather Underground Camera Uploading has been removed. Cam scripts will need to be updated manually.';
