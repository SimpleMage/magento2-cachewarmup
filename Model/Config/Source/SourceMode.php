<?php

namespace SimpleMage\CacheWarmup\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SourceMode implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'file_upload', 'label' => __('File upload')],
            ['value' => 'static_file_path', 'label' => __('Static file path')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'file_upload' => __('File upload'),
            'static_file_path' => __('Static file path')
        ];
    }
}
