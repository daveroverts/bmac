<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('application', 'bmac');
set('deploy_path', '~/{{application}}');
set('repository', 'git@github.com:daveroverts/bmac.git');

// Hosts

host('vps1.dutchvacc.nl')
    ->setRemoteUser('webmaster')
    ->setPort(16793)
    ->setDeployPath('/home/webmaster/www/booking/home');

host('104.248.84.152')
    ->setRemoteUser('forge')
    ->setDeployPath('/home/forge/bmac.daveroverts.nl');

// Tasks

task('build', function () {
    cd('{{release_path}}');
    run('npm run build');
});

after('deploy:failed', 'deploy:unlock');
