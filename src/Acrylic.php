<?php

namespace Valet;

use Exception;
use ZipArchive;
use Symfony\Component\Process\Process;

class Acrylic
{
    const ZIP_URL = 'http://tenet.dl.sourceforge.net/project/acrylic/Acrylic/0.9.31/Acrylic-Portable.zip';

    /**
     * Install and configure the Acrylic service.
     *
     * @param  OutputInterface  $output
     * @return void
     */
    public static function install($output)
    {
        if (! static::alreadyInstalled()) {
            static::download($output);
        }

        static::createCustomConfigurationFile();

        static::uninstall();

        quietly('cmd /c '.static::path().'\AcrylicController.exe InstallAcrylicService');

        static::flushDns();
    }

    /**
     * Uninstall the Acrylic service.
     *
     * @param  OutputInterface  $output
     * @return void
     */
    public static function uninstall()
    {
        quietly('cmd /c '.static::path().'\AcrylicController.exe UninstallAcrylicService');

        static::flushDns();
    }

    /**
     * Stop the Acrylic service.
     *
     * @return void
     */
    public static function stop()
    {
        quietly('cmd /c '.static::path().'\AcrylicController.exe StopAcrylicServiceSilently');

        static::flushDns();
    }

    /**
     * Restart the Acrylic service.
     *
     * @return void
     */
    public static function restart()
    {
        quietly('cmd /c '.static::path().'\AcrylicController.exe StartAcrylicServiceSilently');

        static::flushDns();
    }

    /**
     * Determine if Acrylic is already installed.
     *
     * @return void
     */
    public static function alreadyInstalled()
    {
        return is_dir(static::path());
    }

    /**
     * Download Acrylic.
     *
     * @param  OutputInterface  $output
     * @return void
     */
    protected static function download($output)
    {
        $output->writeln('<info>Acrylic DNS Proxy is not installed, installing it now...</info>');

        $zipPath = realpath(__DIR__.'/../bin').'/acrylic.zip';

        if (file_put_contents($zipPath, fopen(static::ZIP_URL, 'r')) === false) {
            throw new Exception('We were unable to install Acrylic DNS Proxy.');
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath)) {
            $zip->extractTo(static::path());
            $zip->close();
        } else {
            throw new Exception('We were unable to unzip Acrylic DNS Proxy.');
        }

        @unlink($zipPath);

        $output->writeln('');
    }

    /**
     * Create the custom Acrylic configuration file.
     *
     * @return void
     */
    protected static function createCustomConfigurationFile()
    {
        return static::updateDomain(null, 'dev');
    }

    /**
     * Update the domain used by Acrylic.
     *
     * @param  string  $oldDomain
     * @param  string  $newDomain
     * @return void
     */
    public static function updateDomain($oldDomain, $newDomain)
    {
        $configPath = static::path().'/AcrylicHosts.txt';

        file_put_contents($configPath, '127.0.0.1 *.'.$newDomain.PHP_EOL);

        static::restart();
    }

    /**
     * Get the Acrylic path.
     *
     * @return string
     */
    protected static function path()
    {
        return realpath(__DIR__.'\..\bin').'\acrylic';
    }

    /**
     * Flush DNS.
     *
     * @return void
     */
    protected static function flushDns()
    {
        quietly('cmd /c ipconfig /flushdns');
    }
}
