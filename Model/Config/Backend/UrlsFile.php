<?php

namespace SimpleMage\CacheWarmup\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;

class UrlsFile extends File
{
    /**
     * Return path to directory for upload file
     *
     * @return string
     */
    protected function _getUploadDir()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath() . 'import/warmup/';
    }

    /**
     * Add whether scope info
     *
     * @return true
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    /**
     * Get allowed extensions
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['txt'];
    }

    /**
     * Upload file before config save
     *
     * @return UrlsFile
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $file = $this->getFileData();

        if (!empty($file['tmp_name'])) {
            $uploadDir = $this->_getUploadDir();
            try {
                $uploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->setAllowRenameFiles(true);
                $uploader->save($uploadDir);
                $this->setValue($uploader->getUploadedFileName());
            } catch (\Exception $e) {
                throw new LocalizedException(__('%1', $e->getMessage()));
            }
        } else {
            $this->unsValue();
        }

        return $this;
    }
}
