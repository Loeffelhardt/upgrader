<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Upgrade\Application\Checker\ClassExtendsUpdatedPackageChecker\Fetcher;

use Upgrade\Infrastructure\IO\Filesystem;

class ClassMetaDataFromFileFetcher implements ClassMetaDataFromFileFetcherInterface
{
    /**
     * @var \Upgrade\Infrastructure\IO\Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @param \Upgrade\Infrastructure\IO\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Current spryker packages has full class name in class path
     *
     * @param string $filePath
     *
     * @return string|null null in case non-class file
     */
    public function fetchFQCN(string $filePath): ?string
    {
        $fileContent = $this->filesystem->readFile($filePath);

        preg_match('/^(abstract |)class (?<class>\S+).*/m', $fileContent, $matches);

        if (!isset($matches['class'])) {
            return null;
        }

        $className = $matches['class'];

        preg_match('/^namespace (?<namespace>\S+);/m', $fileContent, $matches);

        if (!isset($matches['namespace'])) {
            $matches['namespace'] = '';
        }

        return $matches['namespace'] . '\\' . $className;
    }

    /**
     * @param string $filePath
     *
     * @return string|null
     */
    public function fetchPackageName(string $filePath): ?string
    {
        preg_match('/(?<basePath>.*\/)src\/.*\.php/', $filePath, $matches);

        if (!isset($matches['basePath'])) {
            return null;
        }

        $composerJsonFile = $matches['basePath'] . 'composer.json';

        if (!$this->filesystem->exists($composerJsonFile)) {
            return null;
        }

        $composerJsonFileContent = $this->filesystem->readFile($composerJsonFile);

        return json_decode($composerJsonFileContent, true, 512, \JSON_THROW_ON_ERROR)['name'] ?? null;
    }
}
