<?php

namespace Simi\Simicustomize\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;

class Branddetails extends ConfigValue
{
    protected $serializer;
    protected $uploaderFactory;
    protected $filesystem;
    protected $requestData;

    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->requestData = $requestData;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    public function beforeSave()
    {
        $value = $this->getValue();

        try{
            $mediapath = $this->filesystem
                ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath();
            $files = $_FILES['brand_detail_banner'];
            if (isset($files['name']) && isset($files['tmp_name']) && is_array($files['tmp_name'])) {
                foreach($files['tmp_name'] as $_id => $tmp_name){
                    if ($tmp_name && isset($files['name'][$_id]) && isset($files['size'][$_id])) {
                        $fileid = [
                            'tmp_name' => $tmp_name,
                            'name' => $files['name'][$_id],
                            'size' => $files['size'][$_id],
                        ];
                        $uploader = $this->uploaderFactory->create(['fileId' => $fileid]);
                        $uploader->setFilesDispersion(false);
                        $uploader->setFilenamesCaseSensitivity(false);
                        $uploader->setAllowRenameFiles(true);
                        $pathFile = 'simiconnector/brand/banner/';
                        $result = $uploader->save($mediapath.$pathFile);
                        if (isset($result['file']) && $result['file']) {
                            $value[$_id]['brand_banner'] = $pathFile.$result['file'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('%1', $e->getMessage()));
        }

        unset($value['__empty']);
        $encodedValue = $this->serializer->serialize($value);
        $this->setValue($encodedValue);
    }
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            $decodedValue = $this->serializer->unserialize($value);
            $this->setValue($decodedValue);
        }
    }
}
