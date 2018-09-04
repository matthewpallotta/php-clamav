<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Matthewpallotta\Clamavphp;

use Matthewpallotta\Clamavphp\Adapter\ClamavSocket as ClamavSocket;
use Matthewpallotta\Clamavphp\Adapter\ClamavScan as ClamavScan;

class ClamavService implements ClamavServiceInterface {

    /*
     * $this->option['clamavScanMode'] = 'local' || 'server' || 'cli'
     * local is the default behaviour
     * This tells the socket to use ether the server settings or
     * just connect to local daemon running via socket pid and not a port.
     */
    public $option = [
        'clamavScanMode' => 'local',
        'clamavMaxFileSize' => 25000000,
        'clamavServerHost' => 'localhost',
        'clamavServerPort' => 3310,
        'clamavServerTimeout' => 30,
        'clamavServerSocketMode' => TRUE,
        'clamavLocalSocket' => '/var/run/clamav/clamav.ctl',
        'clamavChunkSize' => 2048,
    ];

    public function __construct($options = null) {

        if(!extension_loaded('sockets')) {
            return ['message' => "Sockets not enabled"];
        }
            if(is_array($options)) {
                if(isset($options['clamavScanMode'])){
                    $this->option['clamavScanMode'] = $options['clamavScanMode'];
                }

                if(isset($options['clamavMaxFileSize'])){
                    $this->option['clamavMaxFileSize'] = $options['clamavMaxFileSize'];
                }

                if(isset($options['clamavServerHost'])){
                    $this->option['clamavServerHost'] = $options['clamavServerHost'];
                }

                if(isset($options['clamavServerPort'])){
                    $this->option['clamavServerPort'] = $options['clamavServerPort'];
                }

                if(isset($options['clamavServerTimeout'])){
                    $this->option['clamavServerTimeout'] = $options['clamavServerTimeout'];
                }

                if(isset($options['clamavServerSocketMode'])){
                    $this->option['clamavServerSocketMode'] = $options['clamavServerSocketMode'];
                }

                if(isset($options['clamavLocalSocket'])){
                    $this->option['clamavLocalSocket'] = $options['clamavLocalSocket'];
                }

                if(isset($options['clamavChuckSize'])){
                    $this->option['clamavChunkSize'] = $options['clamavChunkSize'];
                }
        }

    }

    public function sendToScanner($file)
    {
        $response = null;
        $openedFile = null;

        $socket = new ClamavSocket();
        $checkSocket = $socket->checkSocket($this->option);
        if($checkSocket['message'] == "ClamAV is Alive!") {
            $openedFile = fopen($file, "rb");
            /*
             * Check is file exists or opens
             */
            if(!$openedFile) {
                return ['message' => 'File not found or unable to open'];
            }

            $openedFilesize = filesize($file);

            if($openedFilesize <= $this->option['clamavMaxFileSize']) {
                $clamavScan = new ClamavScan();
                $response = $clamavScan->scan($openedFile, $openedFilesize, $this->option);
            } else {
                $response =  ['message' => 'File is to large for clamav\'s ' . $this->options['clamavMaxFilesize'] . '. This file is: ' . $openedFilesize];
            }
            fclose($openedFile);
            return $response;


        } else {
            return ['message' => 'ClamAV is not available.'];
        }
    }

    public function checkClamav() {
        $response = null;
        /*
         * Send Ping to ClamAV Service
         * Want a better way to handle this
         */
        switch($this->option['clamavScanMode']){
            case "cli":
                break;
            default:
                $socket = new ClamavSocket();
                $response = $socket->checkSocket($this->option);
        }
        return $response;

/*
        $openSocket = $socket->openSocket($this->option);
        if(isset($openSocket['message'])) {
            return $openSocket;
        }
        $clamavScan = new ClamavScan();
        $checkClamAvListening = $clamavScan->send($openSocket, 'PING', 4);
        $socket->closeSocket($openSocket);

        if ($checkClamAvListening['message'] == "PONG") {
            return ['message' => 'ClamAV is alive!'];
        } else {
            return ['message' => 'ClamAV is not running!'];
        }*/
    }

    public function hello() {
        return ["messsage" => "hello"];
    }

}