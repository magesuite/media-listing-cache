<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Model\Directories\GetDirectoryTree;

class CacheDirectoryTree
{
    const TYPE_IDENTIFIER = 'new_media_gallery_directory_tree';
    const TWO_WEEKS_IN_SECONDS = 1209600;

    protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache;

    public function __construct(
        \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache
    ) {
        $this->cache = $cache;
    }

    public function aroundExecute(\Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree $subject, callable $proceed): array
    {
        $identifier = self::TYPE_IDENTIFIER;
        $data = $this->cache->load($identifier);

        if (!$data) {
            $data = $proceed();
            $this->cache->save(
                serialize($data),
                $identifier,
                [\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing::CACHE_TAG],
                self::TWO_WEEKS_IN_SECONDS
            );
        } else {
            $data = unserialize($data);
        }

        return $data;
    }
}
