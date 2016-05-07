<?php

namespace Valet;

class LaunchDaemon
{
    /**
     * Install the system launch daemon for the Node proxy.
     *
     * @return void
     */
    public static function install()
    {
        $serverPath = realpath(__DIR__.'/../server.php');

        if (windows_os()) {
            $phpPath = exec('where php');

            // http://htname.com/service_2/
            $service = realpath(__DIR__.'/../bin/service.exe');

            $command = 'sc create LaravelValet binPath= "'.$service.' \"'.$phpPath.' -S 127.0.0.1:80 '.$serverPath.'"" DisplayName= "Laravel Valet Server" start= auto';

            static::stop();

            exec('sc delete LaravelValet');

            exec($command);
        } else {
            $contents = str_replace(
                'SERVER_PATH', $serverPath, file_get_contents(__DIR__.'/../stubs/daemon.plist')
            );

            $contents = str_replace('PHP_PATH', exec('which php'), $contents);

            file_put_contents('/Library/LaunchDaemons/com.laravel.valetServer.plist', $contents);
        }
    }

    /**
     * Restart the launch daemon.
     *
     * @return void
     */
    public static function restart()
    {
        if (windows_os()) {
            quietly('net stop LaravelValet');

            exec('net start LaravelValet');
        } else {
            quietly('launchctl unload /Library/LaunchDaemons/com.laravel.valetServer.plist > /dev/null');

            exec('launchctl load /Library/LaunchDaemons/com.laravel.valetServer.plist');
        }
    }

    /**
     * Stop the launch daemon.
     *
     * @return void
     */
    public static function stop()
    {
        if (windows_os()) {
            quietly('net stop LaravelValet');
        } else {
            quietly('launchctl unload /Library/LaunchDaemons/com.laravel.valetServer.plist > /dev/null');
        }
    }

    /**
     * Remove the launch daemon.
     *
     * @return void
     */
    public static function uninstall()
    {
        static::stop();

        if (windows_os()) {
            exec('sc delete LaravelValet');
        } else {
            unlink('/Library/LaunchDaemons/com.laravel.valetServer.plist');
        }
    }
}
