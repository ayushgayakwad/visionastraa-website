<?php
function validate_upload($file, $allowed_extensions, $max_size_bytes) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
            UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
            UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload.",
        ];
        $error_code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        return $upload_errors[$error_code] ?? "An unknown error occurred during file upload.";
    }

    $filename = $file['name'];
    $file_size = $file['size'];
    $tmp_name = $file['tmp_name'];

    if ($file_size > $max_size_bytes) {
        return "File is too large. Maximum size is " . ($max_size_bytes / 1048576) . " MB.";
    }

    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        return "Invalid file type. Allowed types are: " . implode(', ', $allowed_extensions);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);

    $allowed_mime_types = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    $expected_mime = $allowed_mime_types[$file_extension] ?? '';
    if (!$expected_mime || !in_array($mime_type, $allowed_mime_types)) {
         return "Invalid file content. The file type does not match its content.";
    }

    return true;
}
?>
