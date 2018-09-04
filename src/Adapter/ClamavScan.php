<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Matthewpallotta\Clamavphp\Adapter;

use Matthewpallotta\Clamavphp\Adapter\ClamavSocket;

class ClamavScan implements ClamavScanInterface {

    /*
     * Connecting to clamav requires zINSTREAM '<length><data>'
     * 4 byte unsigned integer network byte order
     * Possible use of zIDSESSION to build a Queue system for larger files and higher traffic servers.
     */

    public function __construct($options = null) {

    }

    public function scan($fileHandle, $fileSize, $options) {

        $response = null;

        switch($options['clamavScanMode']) {
            case 'cli':
                break;
            default:
                $zInstream = "zINSTREAM\0";

                $socket = new ClamavSocket();
                $openSocket = $socket->openSocket($options);

                $sendResponse['instream'] = $socket->send($openSocket, $zInstream);

                $chunkDataSent = 0;
                $chunkDataLength = $fileSize;

                //while(!feof($fileHandle)) {
                //while ($chunkDataSent<$chunkDataLength) {
                while ($chunkDataSent<$chunkDataLength) {
                    fseek($fileHandle, $chunkDataSent);
                    $chunk = fread($fileHandle, $options['clamavChunkSize']);
                    $chunkLength = pack("N", strlen($chunk));
                    $chunkLengthResponse = $socket->send($openSocket, $chunkLength);
                    $chunkDataResponse = $socket->send($openSocket, $chunk);
                    $chunkDataSent += $chunkDataResponse['written'];
                }
                /*
                 * Currently do not need to send zero string to Clamav with this code.
                 * Leaving it here for the time being for update to how a file is sent to clamvav host socket.
                 */
                $endInstream = pack("N", mb_strlen("")) . "";
                $response = $socket->send($openSocket, $endInstream, 1);
                return $response;

        }

    }

}