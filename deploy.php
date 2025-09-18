<?php
//
//namespace Deployer;
//
//require 'recipe/laravel.php';
//require 'contrib/npm.php';
//require 'contrib/rsync.php';
//
/////////////////////////////////////
//// Config
/////////////////////////////////////
//
//set('application', 'layanan-soca-2');
//set('repository', 'git@github.com:Sumapala-Integrasi-Solusi/layanan-soca-2.git'); // Git Repository
//set('ssh_multiplexing', true);  // Speed up deployment
////set('default_timeout', 1000);
//
//set('rsync_src', function () {
//    return __DIR__; // If your project isn't in the root, you'll need to change this.
//});
//
//// Configuring the rsync exclusions.
//// You'll want to exclude anything that you don't want on the production server.
//add('rsync', [
//    'exclude' => [
//        '.git',
//        '/vendor/',
//        '/node_modules/',
//        '.github',
//        'deploy.php',
//    ],
//]);
//
//// Set up a deployer task to copy secrets to the server.
//// Grabs the dotenv file from the github secret
////task('deploy:secrets', function () {
////    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
////    upload('.env', get('deploy_path') . '/shared');
////});
//
/////////////////////////////////////
//// Hosts
/////////////////////////////////////
//
//host('soca.focuson.id') // Name of the server
//->setHostname('103.172.204.114') // Hostname or IP address
//->set('remote_user', 'administrator') // SSH user
//->set('branch', 'soca-staging-focuson') // Git branch
//->set('deploy_path', '/var/www/layanan-soca-2'); // Deploy path
//
//after('deploy:failed', 'deploy:unlock');  // Unlock after failed deploy
//
/////////////////////////////////////
//// Tasks
/////////////////////////////////////
//
//desc('Start of Deploy the application');
//
//task('deploy', [
//    'deploy:prepare',
//    'rsync',                // Deploy code & built assets
//    'deploy:secrets',       // Deploy secrets
//    'deploy:vendors',
//    'deploy:shared',        //
//    'artisan:storage:link', //
//    'artisan:view:cache',   //
//    'artisan:config:cache', // Laravel specific steps
//    'artisan:migrate',      //
//    'artisan:queue:restart',//
//    'deploy:publish',       //
//]);
//
//desc('End of Deploy the application');
//
