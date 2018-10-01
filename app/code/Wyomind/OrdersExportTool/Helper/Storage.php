<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

/**
 *
 */
class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_ioWrite = null;
    protected $_ioFtp = null;
    protected $_ioSftp = null;
    protected $_directoryList = null;
    protected $_messageManager = null;
    protected $_dateTime = null;
    protected $_storeManager = null;
    protected $_directoryRead = null;
    public $coreHelper = null;
    protected $_ext = [
        1 => 'xml',
        2 => 'txt',
        3 => 'csv',
        4 => 'tsv',
        5 => 'din'
    ];

    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\Filesystem\Io\Ftp $ioFtp,
            \Magento\Framework\Filesystem\Io\Sftp $ioSftp,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
            \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
            \Magento\Store\Model\StoreManager $storeManager,
            \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
            \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->_ioFtp = $ioFtp;
        $this->_ioSftp = $ioSftp;
        $this->_ioWrite = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_messageManager = $messageManager;
        $this->_dateTime = $dateTime;
        $this->_directoryList = $directoryList;
        $this->_storeManager = $storeManager;
        $this->_directoryRead = $directoryRead->create($this->getAbsoluteRootDir());
        $this->coreHelper = $coreHelper;
        parent::__construct($context);
    }

    /**
     * Get the root dir
     * @return string
     */
    public function getAbsoluteRootDir()
    {
        $rootDirectory = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        return $rootDirectory;
    }

    /**
     * Get file type
     * @return string
     */
    public function getFileType($type)
    {
        return $this->_ext[$type];
    }

    /**
     * Get the file name
     * @param string $temp
     * @param string $increment
     * @return sring
     */
    public function getFileName(
    $dateFormat,
            $name,
            $type,
            $currentTime,
            $temp = '.temp',
            $increment = null
    )
    {
        $nameTmp = $this->_dateTime->date($dateFormat, $currentTime);
        $fileNameOutput = str_replace('{f}', $name, $nameTmp);
        return $fileNameOutput . $increment . "." . $this->getFileType($type) . $temp;
    }

    /**
     * Return the file name
     * @param type $model
     * @return string
     */
    public function getFile($model)
    {
        $types = [
            'none',
            'xml',
            'txt',
            'csv',
            'tsv',
            'din'
        ];
        $ext = $types[$model->getType()];
        $date = $this->_dateTime->date($model->getDateFormat(), strtotime($model->getUpdatedAt()));
        $fileName = preg_replace('/^\//', '', $model->getPath() . str_replace('{f}', $model->getName(), $date) . '.' . $ext);
        return $fileName;
    }

    /**
     * Return the file url
     * @param type $file
     * @return string
     */
    public function getFileUrl($file)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . $file;
    }

    /**
     * Open a file with write permission
     * @param string $path
     * @param string $file
     * @return file interface
     * @throws \Exception
     */
    public function openDestinationFile(
    $path,
            $file
    )
    {
        $io = null;
        $this->_ioWrite->create($path);

        if (!$this->_ioWrite->isWritable($path)) {
            throw new \Exception(__('File "%1" cannot be saved.<br/>Please, make sure the directory "%2" is writeable by web server.', $file, $path));
        } else {
            $io = $this->_ioWrite->openFile($path . $file, 'w');
        }

        return $io;
    }

    /**
     * Upload a file to a ftp server
     * @param boolean $useSftp
     * @param boolean $ftpPassive
     * @param string  $ftpHost
     * @param string  $ftpLogin
     * @param string  $ftpPassword
     * @param string  $ftpDir
     * @param string  $file
     * @return boolean
     */
    public function ftpUpload(
    $useSftp,
            $ftpPassive,
            $ftpHost,
            $ftpLogin,
            $ftpPassword,
            $ftpDir,
            $path,
            $file,
            $ftpPort = null
    )
    {

        if ($useSftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }

        $rtn = false;
        try {
            $host = str_replace(["ftp://", "ftps://"], "", $ftpHost);
            if ($useSftp && $ftpPort != null) {
                $host .= ":" . $ftpPort;
            }
            $ftp->open(
                    [
                        'host' => $host,
                        'port' => $ftpPort, // only ftp
                        'user' => $ftpLogin,
                        'username' => $ftpLogin, // only sftp
                        'password' => $ftpPassword,
                        'timeout' => '120',
                        'path' => $ftpDir,
                        'passive' => $ftpPassive // only ftp
                    ]
            );

            if ($useSftp) {
                $ftp->cd($ftpDir);
            }

            if (!$useSftp && $ftp->write($file, $this->getAbsoluteRootDir() . $path . $file)) {
                if ($this->coreHelper->isAdmin()) {
                    $this->_messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                }
                $rtn = true;
            } elseif ($useSftp && $ftp->write($file, $this->getAbsoluteRootDir() . $path . $file)) {
                if ($this->coreHelper->isAdmin()) {
                    $this->_messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                }
                $rtn = true;
            } else {
                if ($this->coreHelper->isAdmin()) {
                    $this->_messageManager->addError(sprintf(__("Unable to upload '%s'on %s"), $file, $ftpHost) . ".");
                }
                $rtn = false;
            }
        } catch (\Exception $e) {
            if ($this->coreHelper->isAdmin()) {
                $this->_messageManager->addError(__("Ftp upload error : ") . $e->getMessage());
            }
        }
        $ftp->close();
        return $rtn;
    }

    /**
     * Send a mail
     * @param array  $filenames
     * @param string $path
     * @param string $mailto
     * @param string $subject
     * @param string $message
     * @param string $type
     * @return boolean
     */
    public function mailWithAttachment(
    $filenames,
            $path,
            $mailto,
            $subject,
            $message,
            $type = null
    )
    {
        $mail = new \Magento\Framework\Mail\Message();
        $mail->setType(\Zend_Mime::MULTIPART_MIXED);
        $mail->setBodyHtml($message);
        $mail->addTo($mailto, $mailto);
        $mail->setSubject($subject);

        if (!is_array($filenames)) {
            $filenames = [$filenames];
        }

        foreach ($filenames as $filename) {
            $mail->createAttachment(
                    $this->_directoryRead->readFile($path . $filename), ($type == null) ? \Zend_Mime::TYPE_OCTETSTREAM : "text/" . $type, \Zend_Mime::DISPOSITION_INLINE, \Zend_Mime::ENCODING_BASE64, basename($filename)
            );
        }

        return $mail->send();
    }

}
