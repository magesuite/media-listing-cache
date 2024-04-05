<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Controller\Adminhtml\Directories\Create;

class FlushDirectoryTreeCache
{
    protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache;

    public function __construct(
        \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache
    ) {
        $this->cache = $cache;
    }

    public function afterExecute(\Magento\MediaGalleryUi\Controller\Adminhtml\Directories\Create $subject, \Magento\Framework\Controller\Result\Json $result): \Magento\Framework\Controller\Result\Json
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_ALL, [\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing::CACHE_TAG]);
        return $result;
    }
}
