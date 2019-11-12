<?php

namespace Check;

class InternetCheck
{
    /*
     * Name of the lock file.
     */
    const LOCKFILE = 'check.lock';

    /*
     * Timeout in minutes until LOCKFILE is considered invalid.
     */
    const LOCKFILE_TIMEOUT = 15;

    /*
     * Timeout in minutes until the router is restarted.
     * Should be less than LOCKFILE_TIMEOUT.
     */
    const RESTART_TIMEOUT = 10;

    /*
     * Timeout in seconds before internet connectivity is checked again.
     */
    const BASE_TIMEOUT = 10;

    public function run()
    {
        $this->log('Starting at ' . (new \DateTime())->format('Y-m-d H:i:s'));

        if ($this->isLocked()) {
            $this->log('A process is already running. Exiting.');
            exit();
        }

        $this->log('Locking and checking.');
        $this->lock();

        if ($this->hasInternetConnectivity()) {
            $this->log('Has internet connectivity. Unlocking and exiting.');
            $this->unlock();
            exit();
        }

        $this->log('No internet connectivity. Retrying.');

        list($maxTries, $totalTries) = $this->retryConnection();

        //  Internet connectivity not returned after RESTART_TIMEOUT minutes
        $this->log('Restarting router after ' . $this->calculateOfflineTime($maxTries, $totalTries) . ' seconds of no connectivity.');

        $this->restartRouter();

        $this->log('Router restarting. Unlocking and exiting.');
        $this->unlock();
    }

    private function log($message)
    {
        file_put_contents('internetcheck.log', $message . PHP_EOL, FILE_APPEND);
    }

    private function lock()
    {
        file_put_contents(self::LOCKFILE, '');
    }

    private function unlock()
    {
        unlink(self::LOCKFILE);
    }

    private function isLocked()
    {
        $fileExists = file_exists(self::LOCKFILE);

        if ( ! $fileExists) {
            return false;
        }

        $creationTime = (new \DateTime())->setTimestamp(filectime(self::LOCKFILE));

        $checkTime = (new \DateTime())->sub(new \DateInterval('PT' . self::LOCKFILE_TIMEOUT . 'M'));

        if ($checkTime > $creationTime) {
            $this->log('Previous lockfile is older than ' . self::LOCKFILE_TIMEOUT . ' minutes. Unlocking and proceeding.');
            $this->unlock();

            return false;
        }

        return true;
    }

    private function hasInternetConnectivity()
    {
        return (bool)@fsockopen('www.google.com', 80, $iErrno, $sErrStr, 5);
    }

    public function retryConnection()
    {
        $tries = 0;
        $maxTries = 5;
        $totalTries = 0;

        $end = (new \DateTime())->add(new \DateInterval('PT' . self::RESTART_TIMEOUT . 'M'));

        do {
            sleep(self::BASE_TIMEOUT + self::BASE_TIMEOUT * $tries);

            if ($this->hasInternetConnectivity()) {
                $this->log('Has internet connectivity again after ' . $this->calculateOfflineTime($maxTries, $totalTries) . ' seconds. Unlocking and exiting.');
                $this->unlock();
                exit();
            }

            if ($tries < $maxTries) {
                $tries++;
            }

            $totalTries++;
        } while ((new \DateTime()) < $end);

        return [$maxTries, $totalTries];
    }

    private function calculateOfflineTime($maxTries, $totalTries)
    {
        $offlineTime = self::BASE_TIMEOUT;

        if ($totalTries < $maxTries) {
            $maxTries = $totalTries;
        }

        for ($i = 1; $i <= $maxTries; $i++) {
            $offlineTime += (self::BASE_TIMEOUT * $i);
        }

        return $offlineTime + ($totalTries - $maxTries) * (self::BASE_TIMEOUT * $maxTries);
    }

    private function restartRouter()
    {
        exec('node src/restart.js');
    }
}