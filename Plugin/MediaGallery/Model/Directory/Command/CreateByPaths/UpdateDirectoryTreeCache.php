<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGallery\Model\Directory\Command\CreateByPaths;

class UpdateDirectoryTreeCache
{
    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache,
    ) {}

    public function afterExecute(\Magento\MediaGallery\Model\Directory\Command\CreateByPaths $subject, ?array $result, array $paths): void
    {
        foreach ($paths as $path) {
            $this->cache->addDirectoryToTreeCache($path);
        }
    }
}

