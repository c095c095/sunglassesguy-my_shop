<?php

include_once __DIR__ . "/../config/config_website.php";

/**
 * Checks if the provided file is an image based on its extension.
 *
 * @param array $file An associative array containing file information, typically from $_FILES.
 *                    The array should have a 'name' key with the file name.
 * @return bool Returns true if the file has an allowed image extension, false otherwise.
 */
function is_image($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return !empty($ext) && in_array($ext, ALLOWED_IMAGE_TYPES);
}

/**
 * Generates a unique image name based on the current date, time, and a random UUID.
 *
 * @param array $file An associative array containing file information, typically from $_FILES.
 * @return string A unique image name with the format 'IMG_YYYYMMDDHHMMSS_UUID.ext'.
 */
function generate_image_name($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $datetime = new DateTime();
    $uuid = bin2hex(random_bytes(16));
    $name = 'IMG_' . $datetime->format('YmdHis') . '_' . $uuid . ".$ext";
    return $name;
}

/**
 * Uploads an image to the specified directory.
 *
 * @param array $file The uploaded file information from the $_FILES superglobal.
 * @param string $name The desired name for the uploaded file.
 * @param string $path The target directory path for the uploaded file. Default is an empty string.
 * @return bool Returns true if the file was successfully uploaded, false otherwise.
 */
function upload_image($file, $name, $path = "") {
    $base_upload_dir = __DIR__ . '/../../upload/';
    $upload_dir = realpath($base_upload_dir . $path);

    if ($upload_dir === false || strpos($upload_dir, realpath($base_upload_dir)) !== 0) {
        return false;
    }

    $target_file = $upload_dir . '/' . basename($name);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = @getimagesize($file["tmp_name"]);
    if ($check === false || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > MAX_IMAGE_SIZE) {
        return false;
    }

    if (!in_array($imageFileType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }

    if (!is_dir($upload_dir)) {
        return false;
    }

    $counter = 1;
    $file_name_no_ext = pathinfo($target_file, PATHINFO_FILENAME);
    while (file_exists($target_file)) {
        $target_file = $upload_dir . '/' . $file_name_no_ext . "($counter)." . $imageFileType;
        $counter++;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return true;
    }

    return false;
}