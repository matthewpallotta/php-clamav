<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/27/18
 * Time: 1:57 PM
 */

namespace Matthewpallotta\Clamavphp\Adapter;

interface ClamavScanInterface {
    public function send($socket, $chunk, $length);
}