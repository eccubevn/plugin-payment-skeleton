<?php

namespace Plugin\Onepay\Service\Payment\Method;

use Eccube\Common\EccubeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Eccube\Service\Payment\PaymentMethodInterface;
use Eccube\Service\Payment\PaymentResult;
use Eccube\Service\Payment\PaymentDispatcher;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\Onepay\Repository\ConfigRepository;

abstract class RedirectLinkGateway implements PaymentMethodInterface
{
    /**
     * @var \Eccube\Entity\Order
     */
    protected $Order;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /** @var EccubeConfig */
    protected $eccubeConfig;

    /** @var ContainerInterface */
    protected $container;

    /**
     * RedirectLinkGateway constructor.
     *
     * @param \Eccube\Entity\Order $Order
     * @param \Symfony\Component\Form\FormInterface $form
     * @param OrderStatusRepository $orderStatusRepository
     * @param PurchaseFlow $purchaseFlow
     * @param ConfigRepository $configRepository
     * @param EccubeConfig $eccubeConfig
     * @param ContainerInterface $container
     */
    public function __construct(
        \Eccube\Entity\Order $Order,
        \Symfony\Component\Form\FormInterface $form,
        OrderStatusRepository $orderStatusRepository,
        PurchaseFlow $purchaseFlow,
        ConfigRepository $configRepository,
        EccubeConfig $eccubeConfig,
        ContainerInterface $container
    ) {
        $this->Order = $Order;
        $this->form = $form;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->purchaseFlow = $purchaseFlow;
        $this->configRepository = $configRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @return PaymentResult
     */
    public function verify()
    {
        $result = new PaymentResult();
        $result->setSuccess(true);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return PaymentResult
     */
    public function checkout()
    {
        $result = new PaymentResult();
        $result->setSuccess(true);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return PaymentDispatcher
     * @throws \Eccube\Service\PurchaseFlow\PurchaseException
     */
    public function apply()
    {
        $OrderStatus = $this->orderStatusRepository->find(OrderStatus::PENDING);
        $this->Order->setOrderStatus($OrderStatus);

        $this->purchaseFlow->prepare($this->Order, new PurchaseContext());

        $url = $this->getCallUrl();
        $response = new RedirectResponse($url);
        $dispatcher = new PaymentDispatcher();
        $dispatcher->setResponse($response);
        return $dispatcher;

    }

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @return $this
     */
    public function setFormType(\Symfony\Component\Form\FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param \Eccube\Entity\Order $Order
     * @return $this
     */
    public function setOrder(\Eccube\Entity\Order $Order)
    {
        $this->Order = $Order;
        return $this;
    }

    /**
     * Generate url endpoint which will be redirect to process payment
     *
     * @return string
     */
    abstract public function getCallUrl();

    /**
     * Handle response via Request object
     *
     * @param Request $request
     * @return mixed
     */
    abstract public function handleRequest(Request $request);
}

