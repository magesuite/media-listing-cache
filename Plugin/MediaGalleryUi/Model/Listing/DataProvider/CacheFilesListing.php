<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Model\Listing\DataProvider;

class CacheFilesListing
{
    protected const ONE_DAY = 86400;

    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \MageSuite\MediaListingCache\Model\Cache\CacheKeyGenerator $cacheKeyGenerator
    ) {}

    public function aroundGetData(\Magento\MediaGalleryUi\Model\Listing\DataProvider $subject, callable $proceed): array
    {
        $cacheKey = $this->cacheKeyGenerator->generate($subject->getSearchCriteria());
        $data = $this->cache->load($cacheKey);

        if (!$data) {
            $data = $proceed();
            $this->cache->save(
                $this->serializer->serialize($data),
                $cacheKey,
                [\MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage\CacheFilesCollection::FILES_COLLECTION_TAG],
                self::ONE_DAY
            );
        } else {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
    }
}
