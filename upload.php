<?php
require_once("../../../wp-load.php");
require_once './vendor/autoload.php';
$config = new \Flow\Config();

$file = new \Flow\File($config);
$request = new \Flow\Request($config);


if ($_SERVER['REQUEST_METHOD'] === 'GET' ) {


    $chunkDir = 'app/.tmp' . DIRECTORY_SEPARATOR . $file->getIdentifier();
    if (!file_exists($chunkDir)) {
        mkdir($chunkDir);
        chmod($chunkDir, 0777);
    }
    $config->setTempDir($chunkDir);
    $file = new \Flow\File($config);


    if ($file->checkChunk()) {
        header("HTTP/1.1 200 Ok");
    } else {
        echo ' ::: chunk checked okay, there was no content';
        header("HTTP/1.1 204 No Content");
        return ;
    }

} else {

    $file = new \Flow\File($config);



    if ($file->validateChunk()) {
        // echo ' ::: chunk validate okay ::: ', "\n";
        $file->saveChunk();
        saveChannelImage($file->getIdentifier());


    } else {
        // error, invalid chunk upload request, retry
        echo ' ::: chunk didn\'t validate okay';
//        header("HTTP/1.1 400 Bad Request");
        return ;
    }

//    die();
}


function saveChannelImage($identifier) {
    global $wpdb;

    // echo "[identifier] $identifier";
    $channel_id = $_REQUEST['channel'];

    $config = new \Flow\Config();

    $tmpDir = '../../uploads/.tmp';
    $chunkDir = $tmpDir . DIRECTORY_SEPARATOR . $identifier;
    if (!file_exists($tmpDir)) mkdir($tmpDir,0777,false);
    if (!file_exists($chunkDir)) mkdir($chunkDir,0777,false);

    $config->setTempDir($chunkDir);
    $file = new \Flow\File($config);

    $channelImgDir = '../../uploads/channels';

    if (!file_exists($channelImgDir)) {
        mkdir($channelImgDir);
        chmod($channelImgDir, 0777);
        // echo '... created uploads/channels folder', "\n";
    }

    $request = new \Flow\Request();
    $imgPath = $channelImgDir . DIRECTORY_SEPARATOR . $request->getFileName();

    // echo '[upload path]', $imgPath, "\n";
    if (file_exists($imgPath)) unlink($imgPath);

    if (\Flow\Basic::save($imgPath, $config, $request)) {
        // echo "Hurray, file was saved in " .$imgPath, "\n";
        // echo '[upload channel image] updating channel '. get_term($channel_id)->name.' ('.$channel_id.')', "\n";
        update_term_meta($channel_id, 'cutv_channel_img', $request->getFileName());

        header('Content-Type: application/json');
        echo json_encode($request->getFileName());

    }

}
