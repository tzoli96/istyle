<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\CustomerExtend\Plugin\Frontend\Magento;

class CopyPassword
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * CopyPassword constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {

        $this->request = $request;
    }

    public function beforeExecute(
        $subject
    ) {
        if($this->request->getParam("password") && !$this->request->getParam("password_confirmation"))
        {
            $params = $this->request->getParams();
            $params["password_confirmation"] = $params["password"];
            $this->request->setParams($params);
            if($this->request->isPost()) {
                $params = $this->request->getPost();
                $params["password_confirmation"] = $params["password"];
                $this->request->setPost($params);
            }
        }
        return [];
    }
}
