<?php

namespace Magebees\Navigationmenupro\Controller\Adminhtml\Menudata;

class RefreshMenu extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        
        $dir = $this->_objectManager->create('Magebees\Navigationmenupro\Helper\Data')->getDirectoryPath();
        $message = "Menu Refresh Successfully completed!";
        $refresh = false;
        try {
            $files = glob($dir . "*"); // get all file names
            if (!empty($files)) {
                foreach ($files as $file) { // iterate files
                    if (is_file($file)) {
                        $result = unlink($file); // delete file
                        if ($result) {
                            $refresh = true;
                        }
                    }
                }
            } else {
                $refresh = true;
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, null, 'navigationmenu.log');
        }
        if (!$refresh) {
            $message = "Menu is not refresh successfully!";
        }
        $this->getResponse()->setBody($message);
    }
    protected function _isAllowed()
    {
        return true;
    }
}
