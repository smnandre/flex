<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex;

use Composer\Composer;
use Composer\Downloader\TransportException;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Downloader
{
    private $composer;
    private $io;
    private $sess;

    public function __construct(Composer $composer, IoInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Decodes a JSON HTTP response body.
     *
     * @param $path The path to get on the Flex server
     */
    public function getContents($path)
    {
        if (null === $this->sess) {
            $this->sess = bin2hex(random_bytes(16));
        }

        $rfs = Factory::createRemoteFilesystem($this->io, $this->composer->getConfig());
        $url = 'https://flex.symfony.com/'.ltrim($path, '/').(false === strpos($path, '&') ? '?' : '&' ).'s='.$this->sess;
        $json = new JsonFile($url, $rfs, $this->io);

        try {
            return $json->read();
        } catch (\RuntimeException $e) {
            if (($ex = $e->getPrevious()) instanceof TransportException) {
                if (0 !== $ex->getCode() && 404 == $ex->getCode()) {
                    return;
                }

                throw $e;
            }
        }
    }
}