<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'w91136je.beget.tech');

// Project repository
set('repository', '');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false); 

// Shared files/dirs between deploys
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server
set('writable_dirs', []);

// Local and remote domain names
set('domain_local', 'wordpress.test');
set('domain_remote', 'w91136je.beget.tech');

// Hosts

host('w91136je@w91136je.beget.tech')->set('deploy_path', '~/{{application}}');

// Tasks

/**
 * Test task
 */
task('test', function () {
    $php_version = run('php -v');
    writeln("Current PHP version: $php_version");
});

/**
 * Push code from local folder to remote server
 */
task('push:code', function () {
    $project_path = runLocally('pwd');
    upload($project_path . '/', '{{deploy_path}}/public_html');
});

/**
 * Push database task
 */
task('push:db', function () {
    runLocally('wp db export --add-drop-table current.sql');
    upload('current.sql', '{{deploy_path}}/public_html/current.sql');
    run('cd {{deploy_path}}/public_html && wp db import current.sql');
    run('cd {{deploy_path}}/public_html && wp search-replace "//'.get('domain_local').'" "//'.get('domain_remote').'"');
});

/**
 * Pull database task
 */
task('pull:db', function () {
    run('cd {{deploy_path}}/public_html && wp db export --add-drop-table current.sql');
    download('{{deploy_path}}/public_html/current.sql', 'current.sql');
    runLocally('wp db import current.sql');
    run('wp search-replace "//'.get('domain_remote').'" "//'.get('domain_local').'"');
});

desc('Push code and files from local to remote server');
task('push', [
    'push:code',
    'push:db',
]);

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
