<?php

/**
 * Copyright 2013 Android Holo Colors by Jérôme Van Der Linden
 * Copyright 2010 Android Asset Studio by Google Inc
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


require_once('common-radio.php');
$color = $_GET['color'];
$size = $_GET['size'];
$holo = $_GET['holo'];
$kitkat = (bool)$_GET['kitkat'];
$component = $_GET['component'];

if (isset($color) && isset($size) && isset($holo) && isset($component) && isset($kitkat)) {
    switch ($component) {
        case "radio":
            $radio = new Radio(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-on-focus":
            $radio = new RadioOnFocus(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-on-pressed":
            $radio = new RadioOnPress(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-on-disabled-focus":
            $radio = new RadioDisabledOnFocus(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-off":
            $radio = new RadioOff(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-off-pressed":
            $radio = new RadioOffPress(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-off-focus":
            $radio = new RadioOffFocus(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        case "radio-off-disabled-focus":
            $radio = new RadioDisabledOffFocus(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
        default:
            $radio = new Radio(HOLO_COLORS_COMPONENTS_PATH.'/radio/');
            break;
    }
    $radio->generate_image($color, $size, $holo, $kitkat=false);
}

?>