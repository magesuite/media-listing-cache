<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage;

class CacheFilesCollection
{
    /**
     * @var \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing
     */
    protected $cache;

    const ONE_DAY = 86400;

    const FILES_COLLECTION_TAG = 'files_collection';

    public function __construct(\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache)
    {
        $this->cache = $cache;
    }

    public function aroundGetFilesCollection(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, callable $proceed, $path, $type = null)
    {
        $cacheKey = md5($path . $type);

        $collection = $this->cache->load($cacheKey);

        if ($collection == null) {
            $collection = $proceed($path, $type);

            $this->cache->save(serialize($collection), $cacheKey, [self::FILES_COLLECTION_TAG], self::ONE_DAY);
        } else {
            $collection = unserialize($collection);
        }

        return $collection;
    }

    public function afterUploadFile(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::FILES_COLLECTION_TAG]);

        return $result;
    }

    public function afterDeleteFile(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::FILES_COLLECTION_TAG]);

        return $result;
    }

    public function afterCreateDirectory(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $tags = [\MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree\CacheTreeJson::TREE_JSON_TAG];

        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);

        return $result;
    }

    public function afterDeleteDirectory(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $tags = [\MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree\CacheTreeJson::TREE_JSON_TAG];

        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);

        return $result;
    }
}