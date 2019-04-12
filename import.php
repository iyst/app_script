<?php

require_once 'src/autoload.php';

$service = new Muse\Service\ProssSongService();
//$service->checkColor();
$service->checkJumpNotes();

