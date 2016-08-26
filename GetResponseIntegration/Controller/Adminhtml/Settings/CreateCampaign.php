<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

class CreateCampaign extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Export');
        $lang = substr($block->getStoreLanguage(), 0, 2);

        $params = [];
        $params['name'] = $data['campaign_name'];
        $params['languageCode'] = (isset($lang)) ? $lang : 'EN';
        $params['confirmation'] = [
            'fromField' => ['fromFieldId' => $data['from_field']],
            'replyTo' => ['fromFieldId' => $data['reply_to_field']],
            'subscriptionConfirmationBodyId' => $data['confirmation_body'],
            'subscriptionConfirmationSubjectId' => $data['confirmation_subject']
        ];

        $grApi = new GetResponseAPI3($block->getApiKey());

        echo json_encode($grApi->createCampaign($params));
        die;
    }
}