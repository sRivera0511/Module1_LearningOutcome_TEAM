<?php
require_once 'core.php';
session_destroy();
header('Location: login.php');
exit;
