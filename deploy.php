<?php

namespace Deployer;

require 'contrib/php-fpm.php';
require 'recipe/laravel.php';

// Config

set('application', 'bmac');
set('deploy_path', '~/{{application}}');
set('repository', 'git@github.com:daveroverts/bmac.git');
set('php_fpm_version', '8.0');

// Hosts

host('vps1.dutchvacc.nl')
    ->setRemoteUser('webmaster')
    ->setPort(16793)
    ->setDeployPath('/home/webmaster/www/booking/home');

// Tasks

task('build', function () {
    cd('{{release_path}}');
    run('npm ci');
    run('npm run prod');
});

after('deploy:update_code', 'artisan:migrate');
after('deploy:update_code', 'build');

after('deploy:failed', 'deploy:unlock');

after('deploy', 'php-fpm:reload');
