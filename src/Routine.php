<?php

namespace Torrentz;


class Routine
{
    public function __construct(Params $params, $magnetUrl)
    {
        $downloader = new Downloader($params);
        echo " ... Uploading torrent data " . PHP_EOL;
        $torrent = $downloader->startTorrentDownload($magnetUrl);
        echo " ... Waiting for torrent to download " . PHP_EOL;
        $downloader->waitForDownload($torrent);
        echo " ... Uploading to remote " . PHP_EOL;
        $downloader->uploadToRemote($torrent);
    }
}