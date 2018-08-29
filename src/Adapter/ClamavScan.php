<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Matthewpallotta\Clamavphp\Adapter;

class ClamavScan implements ClamavScanInterface {

    /*
     * Connecting to clamav requires zINSTREAM '<length><data>'
     * 4 byte unsigned integer network byte order
     * Possible use of zIDSESSION to build a Queue system for larger files and higher traffic servers.
     */

    public function __construct($options = null) {

    }

    public function send($socket, $chunk, $length = 2048) {

        $sentData = 0;
        $cmdLength = strlen($chunk);

        /*
         * If a fwrite does not write the full length because socket gets another packet
         * Track the amount written and continue to try and write the rest.
         * May need to include this with stream_select if statement. or move stream_select into while loop.
         */
        while ($sentData< $cmdLength) {
            $fwrite = fwrite($socket, substr($chunk, $sentData));

            $sentData += $fwrite;
        }

        $readSocket = [$socket];
        $writeSocket = NULL;
        $exceptSockets = NULL;
        if (stream_select($readSocket, $writeSocket, $exceptSockets, 5)) {
            foreach ($readSocket as $rSocket) {
                while (!feof($rSocket)) {
                    $response = trim(stream_get_contents($rSocket));

                    return ['message' => $response];
                }
            }

        }

    }


}