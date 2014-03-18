<?php

/**
 * github-deploy
 *
 * Automatically deploy from github with webhooks
 *
 * @author Roberto Ramírez <robertoiran@gmail.com>
 */

include 'lib/config.php';

$data       = json_decode(file_get_contents('php://input'), true);
$commands   = ['whoami'];

try {
    Config::init(__DIR__ . '/config.json');

    preg_match_all('/\w+/', $data['ref'], $pushRef);

    $Repository = Config::getRepository($data['repository']['id'])
        ->validateToken(@$_GET['_token'])
    ;

    $Branch = $Repository->getBranch($pushRef[0][2]);

    if (! is_dir($Branch->getLocalDirectory())) {
        $command = 'git clone --depth=1 --branch=' . $Branch->getName();

        if ($Branch->getSyncSubmodule()) {
            $command .= ' --recursive';
        }

        $commands[] = sprintf('%s %s %s',
            $command,
            $Repository->getURLRemote(),
            $Branch->getLocalDirectory()
        );
    } else {
        chdir($Branch->getLocalDirectory());

        $commands[] = 'git reset --hard';
        $commands[] = 'git pull origin ' . $Branch->getName();

        if ($Branch->getSyncSubmodule()) {
            $commands[] = 'git submodule update --init --recursive';
        }
    }

    $output = [];
    foreach ($commands as $command) {
        $result = [];
        $command = escapeshellcmd($command);

        exec($command . ' 2>&1', $result, $returnCode);

        $output[] = [
            '$ ' . $command,
            $result,
            $returnCode
        ];
    }

    header('HTTP/1.0 200');
    header('Content-type: application/json');

    echo json_encode($output);
} catch (InvalidArgumentException $e) {
    header(
        sprintf('HTTP/1.0 %s %s',
            $e->getCode(),
            $e->getMessage()
        )
    );
} catch (Exception $e) {
    header(
        sprintf('HTTP/1.0 %s %s',
            $e->getCode(),
            $e->getMessage()
        )
    );
}
