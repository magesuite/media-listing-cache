<?php
declare(strict_types=1);

namespace MageSuite\MediaListingCache\Observer;

class CleanCacheAfterFileUpload implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $mediaListingCache,
    ) {}

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $this->mediaListingCache->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [\MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage\CacheFilesCollection::FILES_COLLECTION_TAG]
        );
        $this->mediaListingCache->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing::MEDIA_LISTING_CACHE_TAG]
        );
    }
}
