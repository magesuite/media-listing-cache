<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Controller\Adminhtml\Wysiwyg\Images;

class Thumbnail
{
    /**
     * @var \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing
     */
    protected $cacheMediaListing;

    public function __construct(\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cacheMediaListing)
    {
        $this->cacheMediaListing = $cacheMediaListing;
    }

    public function afterExecute(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Thumbnail $subject, $result)
    {
        $tags = [\MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage\CacheFilesCollection::FILES_COLLECTION_TAG];
        $this->cacheMediaListing->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);

        return $result;
    }
}