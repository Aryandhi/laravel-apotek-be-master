<?php
echo shell_exec('php artisan config:clear');
echo shell_exec('php artisan cache:clear');
echo shell_exec('php artisan config:cache');