<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Model\Listing\DataProvider;

class CacheFilesListing
{
    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $mediaGalleryCache,
        protected \MageSuite\MediaListingCache\Model\Cache\CacheKeyGenerator $cacheKeyGenerator,
    ) {}

    public function aroundGetData(\Magento\MediaGalleryUi\Model\Listing\DataProvider $subject, callable $proceed): array
    {
        $cacheKey = $this->cacheKeyGenerator->generate($subject->getSearchCriteria());
        $data = $this->mediaGalleryCache->getFileListing($cacheKey);

        if ($data === null) {
            $data = $proceed();
            $this->mediaGalleryCache->saveFileListing($cacheKey, $data);
        }

        return $data;
    }
}
