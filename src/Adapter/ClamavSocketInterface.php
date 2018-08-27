<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/27/18
 * Time: 1:52 PM
 */

namespace Matthewpallotta\Clamavphp\Adapter;


interface ClamavSocketInterface
{
    public function openSocket($options);

    public function closeSocket($socket);
}