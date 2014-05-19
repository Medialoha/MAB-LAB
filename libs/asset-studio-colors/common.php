<?php defined('HOLO_COLORS_PATH') or die('DIRECT ACCESS NOT ALLOWED');
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

abstract class Component
{
    public $logger;
    public $_context = "";
    public $_color = "000";
    public $_holo = "light";
    public $_kitkat = false;
    public $_name = "";

    abstract protected function generate_image($color, $size, $holo, $kitkat=false);

    function __construct($name, $ctx="") 
    {
        $this->_context = $ctx;
        $this->_name = $name;
        $this->logger = Logger::getLogger(__CLASS__);
    }


    /***************************
     *
     * Display image in browser
     *
     ***************************/
    function displayImage($image)
    {
        header("Content-type: image/png");
        ImagePNG($image);
        imagedestroy($image);
    }


    /******************************************************
     *
     * Generate image in the good subfolder (date & session id)
     *
     ******************************************************/
    function generateImageFile($image, $size, $holo)
    {
        $id = $_SESSION['asset_colors_id'];
        date_default_timezone_set('UTC');
        $date = date("Ymd");
        $folder = HOLO_COLORS_GEN_DIR   . $date . "/" . $_SESSION['asset_colors_id'] . "/res/drawable-" . $size;
        if (file_exists($folder) == FALSE) {
            mkdir($folder, 0777, true);
        }
        ImagePNG($image, $folder . "/" . str_replace("{{holo}}", $holo, $this->_name));
        imagedestroy($image);
    }

    /***************************
     *
     * Convert hexadecimal to RGB
     *
     ***************************/
    function hex2RGB($hexStr, $returnAsString = false, $seperator = ',')
    {
        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            if ($_SERVER['SERVER_NAME'] == 'www.android-holo-colors.com' || $_SERVER['SERVER_NAME'] == 'android-holo-colors.com' || $_SERVER['SERVER_NAME'] == 'localhost') {
                $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
                $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
                $rgbArray['blue'] = 0xFF & $colorVal;
            } else {
                $rgbArray['blue'] = 0xFF & ($colorVal >> 0x10);
                $rgbArray['red'] = 0xFF & ($colorVal >> 0x8);
                $rgbArray['green'] = 0xFF & $colorVal;
            }
            
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            if ($_SERVER['SERVER_NAME'] == 'www.android-holo-colors.com' || $_SERVER['SERVER_NAME'] == 'android-holo-colors.com' || $_SERVER['SERVER_NAME'] == 'localhost') {
                $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
                $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
                $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
            } else {
                $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
                $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
                $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
            }
        } else {
            return false; //Invalid hex color code
        }
        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
    }

    /***************************
     *
     * Load Transparent PNG
     *
     ***************************/
    function loadTransparentPNG($image_name, $size)
    {
        $image_path = $this->_context . "images/drawable-" . $size . "/" . $image_name;

        $this->logger->debug("loadTransparentPNG : " . $image_path);

        $im = ImageCreateFromPNG($image_path);

        if (!$im) {
            $this->logger->error($image_path . " KO");
        }

        $size = getimagesize($image_path);
        $w = $size[0];
        $h = $size[1];

        // crée l'image de sortie
        $im2 = imagecreatetruecolor($w, $h);
        if (!$im2) {
            $this->logger->error("imagecreatetruecolor " . $image_path . " KO");
        }
        imagealphablending($im2, false);
        imagesavealpha($im2, true);

        // remplit l'image de sortie
        $imcp = imagecopyresampled($im2, $im, 0, 0, 0, 0, $w, $h, $w, $h);
        if (!$imcp) {
            $this->logger->error("copy " . $image_path . " KO");
        }

        $this->logger->debug("loadTransparentPNG OK");

        return $im2;
    }

    /***************************
     *
     * Create a empty transparent image to copy all others into this one
     *
     ***************************/
    function create_dest_image($image_name, $size, $ctx = "")
    {
        $image_path = $this->_context . "images/drawable-" . $size . "/" . $image_name;

        $this->logger->debug("create_dest_image : " . $image_path);

        $size = getimagesize($image_path);
        $w = $size[0];
        $h = $size[1];

        $dest = imagecreatetruecolor($w, $h);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparent);

        $this->logger->debug("create_dest_image OK");
        $result = array($dest, $w, $h);
        return $result;
    }
}

?>