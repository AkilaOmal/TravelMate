<?php
function save_place_image($file)
{
    $result = array('path' => null, 'error' => null);

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $result;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'The image upload did not complete. Please try again.';
        return $result;
    }

    $maximumSize = 5 * 1024 * 1024;
    if (!isset($file['size']) || $file['size'] > $maximumSize) {
        $result['error'] = 'Choose an image no larger than 5 MB.';
        return $result;
    }

    if (empty($file['tmp_name']) || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $result['error'] = 'The uploaded image could not be verified.';
        return $result;
    }

    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($fileInfo === false) {
        $result['error'] = 'Image checking is unavailable on this server.';
        return $result;
    }

    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    $allowedTypes = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    );
    if (!isset($allowedTypes[$mimeType])) {
        $result['error'] = 'Choose a JPEG, PNG, GIF, or WebP image.';
        return $result;
    }

    $uploadDirectory = __DIR__ . '/../uploads/places';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
        $result['error'] = 'The image upload folder could not be created.';
        return $result;
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . '/' . $fileName)) {
        $result['error'] = 'The image could not be saved. Check the uploads folder permissions.';
        return $result;
    }

    $result['path'] = 'uploads/places/' . $fileName;
    return $result;
}

function delete_place_image_file($image)
{
    $prefix = 'uploads/places/';
    if (!is_string($image) || strpos($image, $prefix) !== 0) {
        return;
    }

    $fileName = substr($image, strlen($prefix));
    if ($fileName === '' || basename($fileName) !== $fileName) {
        return;
    }

    $filePath = __DIR__ . '/../uploads/places/' . $fileName;
    if (is_file($filePath) && !unlink($filePath)) {
        error_log('TravelMate could not remove uploaded place image: ' . $filePath);
    }
}
