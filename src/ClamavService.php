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
        'clamavServerMode' => TRUE,
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
        $checkClamAVisAlive = $this->checkClamavService();
        /*
         * If we are unable to detect if clamav is installed or listening then
         * return message.
         */
        if($checkClamAVisAlive['message'] != 'ClamAV is alive!') {
            var_dump("check is Alive", $checkClamAVisAlive);
            return $checkClamAVisAlive;
        }
        $zInstream = "zINSTREAM\0";

        $socket = new ClamavSocket();
        $openSocket = $socket->openSocket($this->option);


        $openedFile = fopen($file, "rb");
        /*
         * Check to see if file can be opened.
         * if not return message
         */
        if(!$openedFile) {
            return ['message' => 'File not found or unable to open'];
        }
        $openedFilesize = filesize($file);

        /*
         * Check to make sure the file scanning is allowed based on clamav file size.
         */
        if($openedFilesize <= $this->option['clamavMaxFileSize']) {

            $clamavScan = new ClamavScan();
            $clamavScan->send($openSocket, $zInstream, strlen($zInstream));
            /*
             * Search the file to the end of the file or loop through the chucked size till the end of the file.
             * If looping through the chucksize. Write each chunk, but tracking what data is left.
             */
            while(!feof($openedFile)) {

                $openedFileBuffer = fread($openedFile, $openedFilesize);

                /*
                 * $chunkLength is the 4 byte integer in network byte order.
                 * $chunkData is the chuck of data to send to ClamAV
                 */
                $chunkLength = pack("N", strlen($openedFileBuffer));
                $chunkData = $openedFileBuffer;

                $response['DocumentScan'] = $clamavScan->send($openSocket, $chunkLength, strlen($chunkLength));
                $clamavScan->send($openSocket, $chunkData, strlen($chunkData));

            }
            fclose($openedFile);
            /*
             * Currently do not need to send zero string to Clamav with this code.
             * Leaving it here for the time being for update to how a file is sent to clamvav host socket.
             */
            //$endInstream = pack("N", mb_strlen("")) . "";
            //$response = $clamavScan->send($openSocket, $endInstream);
            $socket->closeSocket($openSocket);
            return $response['DocumentScan'];
        } else {
            return ['message' => 'File is to large for clamav\'s ' . $this->options['clamavMaxFilesize'] . '. Your file is: ' . $openedFilesize];
        }
    }

    public function getScan()
    {
        // TODO: Implement getScan() method.
    }

    public function checkClamavService()
    {
        /*
         * Send Ping to ClamAV Service
         * Want a better way to handle this
         */
        $socket = new ClamavSocket();
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
        }
    }

    public function checkScanQueue()
    {
        // TODO: Implement getQueue() method.
    }

    public function hello() {
        return ["msg" => "hello"];
    }

}