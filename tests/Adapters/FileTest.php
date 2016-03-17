<?php

namespace React\Tests\Filesystem\Adapters;

use Clue\React\Block;
use React\EventLoop\LoopInterface;
use React\Filesystem\ChildProcess;
use React\Filesystem\Eio;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Pthreads;

class FileTest extends AbstractAdaptersTest
{
    /**
     * @dataProvider filesystemProvider
     */
    public function testStat(LoopInterface $loop, FilesystemInterface $filesystem)
    {
        $actualStat = lstat(__FILE__);
        $result = Block\await($filesystem->file(__FILE__)->stat(), $loop);
        foreach ($actualStat as $key => $value) {
            if (!is_string($key) || in_array($key, ['atime', 'mtime', 'ctime'])) {
                continue;
            }

            $this->assertSame($actualStat[$key], $result[$key]);
        }

        $this->assertInstanceOf('DateTime', $result['atime']);
        $this->assertEquals($actualStat['atime'], $result['atime']->format('U'));
        $this->assertInstanceOf('DateTime', $result['mtime']);
        $this->assertEquals($actualStat['mtime'], $result['mtime']->format('U'));
        $this->assertInstanceOf('DateTime', $result['atime']);
        $this->assertEquals($actualStat['ctime'], $result['ctime']->format('U'));
    }

    /**
     * @dataProvider filesystemProvider
     */
    public function testRemove(LoopInterface $loop, FilesystemInterface $filesystem)
    {
        $tempFile = $this->tmpDir . uniqid('', true);
        touch($tempFile);
        do {
            sleep(1);
        } while (!file_exists($tempFile));
        Block\await($filesystem->file($tempFile)->remove(), $loop);
        $this->assertFalse(file_exists($tempFile));
    }
}