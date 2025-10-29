<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGallery\Model\Directory\Command\DeleteByPaths;

class UpdateDirectoryTreeCache
{
    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache,
    ) {}

    public function afterExecute(\Magento\MediaGallery\Model\Directory\Command\DeleteByPaths $subject, ?array $result, array $paths): void
    {
        foreach ($paths as $path) {
            $this->cache->removeDirectoryFromDirectoryTree($path);
        }
    }
}

