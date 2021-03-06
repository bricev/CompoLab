<?php

namespace CompoLab\Tests\Domain;

use CompoLab\Domain\Package;
use CompoLab\Domain\Repository;
use CompoLab\Domain\ValueObject\Dir;
use CompoLab\Domain\ValueObject\Url;
use CompoLab\Domain\ValueObject\Version;
use PHPUnit\Framework\TestCase;

final class PackageTest extends TestCase
{
    private static $repository;

    public static function setUpBeforeClass()
    {
        self::$repository = new Repository(
            new Url('https://composer.my-website.com'),
            new Dir(__DIR__ . '/../../cache')
        );
    }

    public function testBuildFromArrayWithoutAsset()
    {
        $this->expectException(\RuntimeException::class);

        Package::buildFromArray(__DIR__ . '/../../cache', [
            'name'    => 'vendor/project',
            'version' => 'dev-master',
        ]);
    }

    public function testBuildFromArray()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../data/composer.json'), true);

        $package = Package::buildFromArray(__DIR__ . '/../../cache', array_merge($json, [
            'version'       => 'dev-master',
            'source'        => [
                'type'      => 'git',
                'url'       => 'git@gitlab.my-website.com:vendor/project.git',
                'reference' => '6a6e0ea9479c821d4b5728c0d3c9840e71085e82',
            ],
        ]));

        $this->assertEquals('vendor/project', $package->getName());
        $this->assertInstanceOf(Version::class, $package->getVersion());

        $packageArray = json_decode(json_encode($package), true);

        $this->assertEquals('git@gitlab.my-website.com:vendor/project.git', $packageArray['source']['url']);

        $this->assertArrayHasKey('name', $packageArray);
        $this->assertArrayHasKey('version', $packageArray);
        $this->assertArrayHasKey('source', $packageArray);
        $this->assertArrayHasKey('minimum-stability', $packageArray);
        $this->assertArrayHasKey('require', $packageArray);
        $this->assertArrayHasKey('require-dev', $packageArray);
        $this->assertArrayHasKey('autoload', $packageArray);
    }
}
