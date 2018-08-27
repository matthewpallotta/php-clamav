<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/9/18
 * Time: 9:00 AM
 */
namespace Matthewpallotta\Clamavphp;

interface ClamavServiceInterface {

    public function sendToScanner($file);

    public function getScan();

    public function checkClamavService();

    public function checkScanQueue();

    public function hello();
}