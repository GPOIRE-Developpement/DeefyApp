<?php

require_once 'vendor/autoload.php';

use iutnc\deefy\dispatch\Dispatcher;
use iutnc\deefy\repository\DeefyRepository;

DeefyRepository::setConfig('db.config.ini');

$action = $_GET['action'] ?? 'default';
$d = new Dispatcher($action);

$d->run();