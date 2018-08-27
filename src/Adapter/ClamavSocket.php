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
        $clamavServer = $options['clamavServerHost'];
        $clamavServerPort = $options['clamavServerPort'];

        $socket = stream_socket_client("tcp://$clamavServer:$clamavServerPort", $errorno, $errorstr, $options['clamavServerTimeout']);
        if(!$socket) {
            $message = "$errorstr ($errorno)";
            return ['message' => $message];
        }

        if ($options['clamavServerMode'] === false && $options['clamavScanMode'] == 'server') {
            stream_set_blocking($socket, FALSE);
        }
        return $socket;

    }

    public function closeSocket($socket) {
            fclose($socket);
    }
}