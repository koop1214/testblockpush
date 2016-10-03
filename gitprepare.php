#!/usr/bin/php5

<?php
/**
 * Created by IntelliJ IDEA.
 * User: undkit
 * Date: 03.10.16
 * Time: 9:43
 *
 * написать консольный php-скрипт, в котором захордкожен полный путь к www-папке портала.
 * Этот скрипт должен делать следующее:
 * 0) этот скрипт в самом начале должен проверить, что он запущен из под пользователя www-data
 * 1) проверить файл nginx-конфига, что путь к веб-папке совпадает с захардкоженным в скрипте
 * 2) git fetch (и убедиться по коду возврата, что все прошло успешно)
 * 3) убедиться, что текущая ветка - master и она up to date
 * 4) сделать git checkout -b master_stable_YYMMDD_His (проверить код возврата)
 * 5) сделать git checkout master (проверить код возврата)
 */

/*захордкоженный полный путь к www-папке портала */
define('WWW_PATH', '/wwwhome/napopravku.ru/www');
/* файл nginx-конфига*/
define('NGINX_SITE_PATH', '/etc/nginx/sites-available/napopravku.ru');
/* правильный юзер */
define('USER', 'www-data');
/* ветка мастер */
define('MASTER_BRANCH', 'refs/heads/master');


/**
 * этот скрипт в самом начале должен проверить, что он запущен из под пользователя www-data
 */
function checkUser()
{
    print('CHECKING USER...' );
    echo 'CHECKING USER...' . "\n";

    $processUser = posix_getpwuid(posix_geteuid());
    $userName = $processUser['name'];

    if ($userName != USER) {

        echo 'Current user ' . $userName . ' is not ' . USER."\n";
        exit;
    }
}

/**
 *  проверить файл nginx-конфига, что путь к веб-папке совпадает с захардкоженным в скрипте
 */
function checkNginxConfig()
{
    echo 'CHECKING NGINX CONFIG...' . "\n";

    try {
        $config = file_get_contents(NGINX_SITE_PATH);
    } catch (Exception $e) {
        echo 'Not exist ' . NGINX_SITE_PATH."\n";
        exit;
    }

    $rootStart = strpos($config, 'root');

    if ($rootStart === false) {
        echo 'No root directive at ' . NGINX_SITE_PATH."\n";
        exit;
    }

    $rootStart = strpos($config, ' ', $rootStart);
    $rootEnd = strpos($config, ';', $rootStart);
    $rootContent = trim(substr($config, $rootStart, $rootEnd - $rootStart));

    if ($rootContent != WWW_PATH) {
        echo 'Root path ' . $rootContent . ' is not ' . WWW_PATH."\n";
        exit;
    }
}


/**
 *   git fetch (и убедиться по коду возврата, что все прошло успешно)
 */
function runGitFetch()
{
    echo 'GIT FETCH...' . "\n";

    exec('git fetch', $output, $fetchResult);

    if ($fetchResult !== 0) {
        echo 'Something wrong: ' . $fetchResult;
        exit;
    }
}

/**
 *  убедиться, что текущая ветка - master и она up to date
 */
function checkMaster()
{
    echo 'CHECKING MASTER...' . "\n";

    $currentBranch = `git symbolic-ref HEAD`;
    if ($currentBranch != MASTER_BRANCH) {
        echo 'Current branch ' . $currentBranch . ' is not ' . MASTER_BRANCH."\n";
        exit;
    }

    `git remote update`;
    $gitStatus = `git status -uno`;
    if (strpos($gitStatus, 'up-to-date') === false) {
        echo 'Master branch is not up-to-date'."\n";
        exit;
    }
}

/**
 *   сделать git checkout -b master_stable_YYMMDD_His (проверить код возврата)
 */
function runGitCheckoutNew()
{
    echo 'GIT CHECKOUT -B...' . "\n";

    $date = new DateTime();
    $dateStr = $date->format('Ymd_His');

    exec('git checkout -b master_stable_' . $dateStr, $output, $checkoutResult);

    if ($checkoutResult !== 0) {
        echo 'Something wrong: ' . $checkoutResult."\n";
        exit;
    }
}

/**
 *   сделать git checkout master (проверить код возврата)
 */
function runGitCheckoutMaster()
{
    echo 'GIT CHECKOUT MASTER...' . "\n";

    exec('git checkout master', $output, $checkoutResult);

    if ($checkoutResult !== 0) {
        echo 'Something wrong: ' . $checkoutResult."\n";
        exit;
    }
}

checkUser();
checkNginxConfig();
runGitFetch();
checkMaster();
runGitCheckoutNew();
runGitCheckoutMaster();
exit;





