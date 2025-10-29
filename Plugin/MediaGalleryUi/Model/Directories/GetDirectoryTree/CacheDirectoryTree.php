<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Model\Directories\GetDirectoryTree;

class CacheDirectoryTree
{
    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $mediaGalleryCache,
    ) {}

    public function aroundExecute(\Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree $subject, callable $proceed): array
    {
        $data = $this->mediaGalleryCache->getDirectoryTree();

        if ($data === null) {
            $data = $proceed();
            $this->mediaGalleryCache->saveDirectoryTree($data);
        }

        return $data;
    }
}
