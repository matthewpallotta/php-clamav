<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Matthewpallotta\Clamavphp\Adapter;

class ClamavSocket implements ClamavSocketInterface{

    public function __construct($options = null) {

    }

    public function openSocket($options) {
        /*
         * Socket should be opened as non-blocking
         * stream_socket_client()
         * stream_set_blocking($stream, FALSE)
         */

        $socket = null;
        $message = null;
        $errorno = null;
        $errorstr = null;

        if($options['clamavScanMode'] != 'cli') {

            $clamavServer = $options['clamavServerHost'];
            $clamavServerPort = $options['clamavServerPort'];

            switch($options['clamavScanMode']) {
                case 'server':
                    $socket = stream_socket_client("tcp://$clamavServer:$clamavServerPort", $errorno, $errorstr, $options['clamavServerTimeout']);
                    break;
                default:
                    $socket = stream_socket_client("unix://".$options['clamavLocalSocket'], $errorno, $errorstr, $options['clamavServerTimeout']);
            }

            if(!$socket) {
                $message = "$errorstr ($errorno)";
                return ['message' => $message];
            }
            /*
             * Check if ClamAV is listening
             */
            fwrite($socket, "PING", 4);
            $pingResponse = fgets($socket, 4);
            if($pingResponse === "PONG") {
                if ($options['clamavServerMode'] === false && $options['clamavScanMode'] == 'server') {
                    stream_set_blocking($socket, FALSE);
                }
                return $socket;
            }
        }
    }

    public function closeSocket($socket) {
            fclose($socket);
    }

    public function checkSocket($socket)
    {
        // TODO: Implement checkSocket() method.
    }
}