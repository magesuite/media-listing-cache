<?php
declare(strict_types=1);

namespace MageSuite\MediaListingCache\Observer;

class CleanCacheAfterFileUpload implements \Magento\Framework\Event\ObserverInterface
{
    protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $mediaListingCache;

    public function __construct(\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $mediaListingCache)
    {
        $this->mediaListingCache = $mediaListingCache;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $this->mediaListingCache->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [\MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage\CacheFilesCollection::FILES_COLLECTION_TAG]
        );
    }
}
