<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree;

class CacheTreeJson
{
    /**
     * @var \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing
     */
    protected $cache;

    const ONE_DAY = 86400;
    const TREE_JSON_TAG = 'tree_json';

    public function __construct(\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache)
    {
        $this->cache = $cache;
    }

    public function aroundGetTreeJson(\Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Tree $subject, callable $proceed)
    {
        $cacheKey = self::TREE_JSON_TAG;

        $json = $this->cache->load($cacheKey);

        if ($json == null) {
            $json = $proceed();

            $this->cache->save($json, $cacheKey, [self::TREE_JSON_TAG], self::ONE_DAY);
        }

        return $json;
    }
}