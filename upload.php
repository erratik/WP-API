<?php

require_once './vendor/autoload.php';
$config = new \Flow\Config();

$file = new \Flow\File($config);

$chunkDir = './app/.tmp' . DIRECTORY_SEPARATOR . $file->getIdentifier();
if (!file_exists($chunkDir)) {
    mkdir($chunkDir);
    chmod($chunkDir, 0777);
}
$config->setTempDir($chunkDir);
$file = new \Flow\File($config);

print_r($file);

//if (\Flow\Basic::save('.' . $file->getIdentifier(), $config, $request)) {
////    // file saved successfully and can be accessed at './final_file_destination'
///*
if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    if ($file->checkChunk()) {
        header("HTTP/1.1 200 Ok");
    } else {
        header("HTTP/1.1 204 No Content");
        return ;
    }

} else {

    if ($file->validateChunk()) {
        $file->saveChunk();
    } else {
        // error, invalid chunk upload request, retry
        header("HTTP/1.1 400 Bad Request");
        return ;
    }
}

/*
if ($file->validateFile() ) {
    $channelImgDir = '../../uploads/channels';
    echo ' ', $channelImgDir;
    if (!file_exists($channelImgDir)) {
        mkdir($channelImgDir);
        chmod($channelImgDir, 0777);
    }
    // File upload was completed
    echo 'File upload was completed';
    $request = new \Flow\Request();
    if (\Flow\Basic::save($channelImgDir . DIRECTORY_SEPARATOR . $request->getFileName(), $config, $request)) {
        echo "Hurray, file was saved in " .$channelImgDir . DIRECTORY_SEPARATOR . $request->getFileName();

        if ( ! add_post_meta( $source_id, 'cuv_channel_img', $new_cat_str, true ) ) {
            update_post_meta($source_id, 'wpvr_source_postCats', $new_cat_str);
        }

    }
} else {
    // This is not a final chunk, continue to upload
}
*/