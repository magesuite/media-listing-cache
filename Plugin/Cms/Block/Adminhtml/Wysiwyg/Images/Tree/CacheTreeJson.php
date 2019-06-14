<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree;

class CacheTreeJson
{

    const ONE_DAY = 86400;
    const TREE_JSON_TAG = 'tree_json';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Cms\Helper\Wysiwyg\Images
     */
    protected $imagesHelper;

    /**
     * @var \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Cms\Helper\Wysiwyg\Images $imagesHelper,
        \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache
    )
    {
        $this->cache = $cache;
        $this->imagesHelper = $imagesHelper;
        $this->request = $request;
    }

    public function aroundGetTreeJson(\Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Tree $subject, callable $proceed)
    {
        $nodeName = $this->request->getParam($this->imagesHelper->getTreeNodeName());
        $cacheKey = sprintf('%s_%s', self::TREE_JSON_TAG, md5($nodeName));

        $json = $this->cache->load($cacheKey);

        if ($json == null) {
            $json = $proceed();

            $this->cache->save($json, $cacheKey, [self::TREE_JSON_TAG], self::ONE_DAY);
        }

        return $json;
    }
}