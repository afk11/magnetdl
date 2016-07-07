<?php

namespace Torrentz;


use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use Transmission\Client;
use Transmission\Model\Torrent;
use Transmission\Transmission;

class Downloader extends EventEmitter
{
    /**
     * @var Params
     */
    private $params;

    /**
     * @var Transmission
     */
    private $transmission;

    public function __construct(Params $params)
    {
        $this->params = $params;
        $this->on('status.download.start', [$this, 'checkDownloadStatus']);
    }

    public function getTransmission()
    {
        if (null === $this->transmission) {
            $client = new Client($this->params->getRpcHost(), $this->params->getRpcPort());
            if ($this->params->doAuthentication()) {
                $client->authenticate($this->params->getRpcUser(), $this->params->getRpcPass());
            }

            $transmission = new Transmission($this->params->getRpcHost(), $this->params->getRpcPort());
            $transmission->setClient($client);

            $this->transmission = $transmission;
        }

        return $this->transmission;
    }

    public function startTorrentDownload($magnetUrl)
    {
        $transmission = $this->getTransmission();
        return $transmission->add($magnetUrl);
    }

    public function checkDownloadStatus(LoopInterface $loop, Torrent $torrent)
    {

    }

    public function waitForDownload(Torrent $torrent)
    {
        $loop = \React\EventLoop\Factory::create();

        $check = function () use ($loop, $torrent) {
            /**
             * @var Torrent[] $results
             */
            $results = $this->getTransmission()->all();
            $found = false;
            foreach ($results as $result) {
                if ($result->getHash() === $torrent->getHash()) {
                    $found = true;
                    $torrent = $result;
                }
            }

            if (!$found) {
                throw new \RuntimeException('Torrent not found?');
            }

            /** @var \Transmission\Model\Torrent $torrent */
            if ($torrent->getPercentDone() == "100") {
                echo " [ download complete ] \n";
                $loop->stop();
            } else {
                $eta = number_format($torrent->getEta() / 60, 2);
                $rate = number_format($torrent->getDownloadRate() / 1024 / 1024, 3);
                echo " [ progress: ".$torrent->getPercentDone()."% | rate: ".$rate."Mb/s | eta: $eta min | peers: " . count($torrent->getPeers()) . " | trackers: " . count($torrent->getTrackers()) . "]\n";
            }
        };

        $loop->addPeriodicTimer(5, $check);
        $check();

        $loop->run();
        return true;
    }



    private function generateUploadDirCmd(Torrent $torrent)
    {
        $dirname = str_replace(" ", "\\ ", $torrent->getName());
        $command = sprintf("sftp -oIdentityFile=%s %s@%s <<EOF
lcd %s/".$dirname."
cd %s
mkdir ".$dirname."
put -r .
EOF",
            $this->params->getSftpIdentityfile(), $this->params->getSftpUser(), $this->params->getSftpHost(),
            $this->params->getDirLocal(),
            $this->params->getDirRemote()
        );

        return $command;
    }


    private function generateUploadCmd(Torrent $torrent)
    {

        $command = sprintf("sftp -oIdentityFile=%s %s@%s <<EOF
lcd '%s/".$torrent->getName()."'
cd '%s'
mkdir '".$torrent->getName()."'
put -r . '".$torrent->getName()."'
EOF",
            $this->params->getSftpIdentityfile(), $this->params->getSftpUser(), $this->params->getSftpHost(),
            $this->params->getDirLocal(),
            $this->params->getDirRemote()
        );

        return $command;
    }

    public function uploadToRemote(Torrent $torrent)
    {
        $output = null;
        $return = null;
        exec($this->generateUploadCmd($torrent), $output, $return);
        
    }

}