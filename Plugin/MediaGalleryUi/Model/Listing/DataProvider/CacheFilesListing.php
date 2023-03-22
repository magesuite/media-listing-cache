<?php

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Model\Listing\DataProvider;

class CacheFilesListing
{
    const ONE_DAY = 86400;

    protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache;

    public function __construct(
        \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache
    ) {
        $this->cache = $cache;
    }

    public function aroundGetData(\Magento\MediaGalleryUi\Model\Listing\DataProvider $subject, callable $proceed): array
    {
        $key = serialize($subject->getSearchCriteria());
        $cacheKey = hash('md5', $key);
        $data = $this->cache->load($cacheKey);

        if (!$data) {
            $data = $proceed();
            $this->cache->save(
                serialize($data),
                $cacheKey,
                [\MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage\CacheFilesCollection::FILES_COLLECTION_TAG],
                self::ONE_DAY
            );
        } else {
            $data = unserialize($data);
        }

        return $data;
    }
}
