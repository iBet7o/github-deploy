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

    if (true == $data['deleted']) {
        $commands = array_merge($commands, $Branch->triggerOnDeleted());
    } elseif (! $Branch->isLocalDirectory()) {
        $commands = array_merge($commands, $Branch->gitClone());
    } else {
        chdir($Branch->getLocalDirectory());

        $commands = array_merge($commands, $Branch->gitPull());
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
