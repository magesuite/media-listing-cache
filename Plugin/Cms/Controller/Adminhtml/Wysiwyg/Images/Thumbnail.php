<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Controller\Adminhtml\Wysiwyg\Images;

class Thumbnail
{
    protected \Magento\Framework\Event\ManagerInterface $eventManager;

    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function afterExecute(\Magento\Cms\Controller\Adminhtml\Wysiwyg\Images\Thumbnail $subject, $result)
    {
        $this->eventManager->dispatch('media_gallery_upload');

        return $result;
    }
}
