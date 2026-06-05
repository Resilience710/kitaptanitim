<?php
/** Çıkış. */
require __DIR__ . '/../includes/init.php';
logout_admin();
redirect(url('admin/login.php'));
