<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'colorui-im-admin-test');

// Project repository
set('repository', 'https://gitee.com/wpjscc/laravel-k8s-demo.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server
set('writable_dirs', []);


// Hosts

host('47.96.15.116')
    ->set('deploy_path', '~/{{application}}')->user('root')->identityFile('~/.ssh/id_rsa');

task('local_test', function(){
    output()->write(runLocally('ls -a'));
});


set('RELEASE_NUM', function(){
     return  runLocally('echo "$(date +%Y%m%d)-$(git rev-parse --short HEAD)"');
});

set('IMAGE_NAME', function(){
    return  parse('registry.cn-shanghai.aliyuncs.com/wpjscc/{{application}}:{{RELEASE_NUM}}');
});


task('build', function(){

    runLocally('docker login registry.cn-shanghai.aliyuncs.com -u jc91715 -p jc510061372');
    runLocally('docker build -t {{IMAGE_NAME}} . -f docker/Dockerfile');
    runLocally('docker push  {{IMAGE_NAME}}');

});


task('deploy1', function(){

    // runLocally('export RELEASE_NUM="$(date +%Y%m%d)-$(git rev-parse --short HEAD)"');
    file_put_contents('./docker/k8s.deployment-test.yaml', parse(file_get_contents('./docker/k8s.deployment-test.tpl.yaml')));
    run('mkdir -p /log/{{application}}.k8sv2.wpjs.cc');
    run('mkdir -p ~/{{application}}');
    upload('./docker', '~/{{application}}');
    run('/opt/kube/bin/kubectl apply -f ~/{{application}}/docker/k8s.deployment-test.yaml');

    // output()->write(runLocally('echo "$(date +%Y%m%d)-$(git rev-parse --short HEAD)"'));

});

set('current_pod' ,function(){
    return run('/opt/kube/bin/kubectl get pod | grep {{application}}| awk \'{print $1}\'');
});

task('update_code', function(){

    // run('/opt/kube/bin/kubectl exec {{current_pod}} -- apt update && apt install git && cd /code && git checkout develop && git pull origin develop');
    // output()->write(runLocally('echo "$(date +%Y%m%d)-$(git rev-parse --short HEAD)"'));

});

// Tasks

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'build',
    'deploy1',

    'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
