<?php
require '../includes/auth.php';
session_destroy();
header("Location: " . BASE . "/auth/login.php");