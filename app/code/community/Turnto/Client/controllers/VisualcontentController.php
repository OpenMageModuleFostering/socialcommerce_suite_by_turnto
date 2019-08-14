<?php

class Turnto_Client_VisualcontentController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (intVal(Mage::getStoreConfig('turnto_admin/vcpinboard/enabled')) != 1) {
            // visual content pinboard not enabled
            $this->norouteAction();
            return;
        }

        $this->loadLayout();

        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'vcpinboard',
            array('template' => 'turnto/client/pinboard.phtml')
        );

        $headBlock = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'vcpinboardjs',
            array('template' => 'turnto/client/vcpinboard_js.phtml')
        );

        $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
        $this->getLayout()->getBlock('head')->append($headBlock);
        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }
}