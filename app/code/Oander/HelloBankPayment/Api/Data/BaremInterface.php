<?php
namespace Oander\HelloBankPayment\Api\Data;

interface BaremInterface
{
    const TABLE_NAME                = 'oander_hellobank_barems';
    const ID                        = 'id';
    const BAREM_NAME                = 'name';
    const BAREM_ID                  = 'barem_id';
    const STATUS                    = 'status';
    const PRIORITY                  = 'priority';
    const MINIMUM_PRICE             = 'min_price';
    const MAXIMUM_PRICE             = 'max_price';
    const INSTALLMENTS_TYPE         = 'installments_type';
    const INSTALLMENTS              = 'installments';
    const DEFAULT_INSTALLMENT       = 'default_installment';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const STATUS_ENABLED            = 1;
    const STATUS_DISABLED           = 0;
    const INSTALLMENTS_TYPE_FIXED   = 2;
    const INSTALLMENTS_TYPE_RANGE   = 3;

    public function getId();

    public function getBaremName();

    /**
     * @param $baremName
     * @return string
     */
    public function setBaremName($baremName);

    /**
     * @return int
     */
    public function getBaremId();

    /**
     * @param $baremId
     * @return int
     */
    public function setBaremId($baremId);


    /**
     *
     * @return int|null
     */
    public function getStatus();

    /**
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     *
     * @return int|null
     */
    public function getPriority();

    /**
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * @return string
     */
    public function getMaxPrice();

    /**
     * @param $price
     * @return string
     */
    public function setMaxPrice($price);

    /**
     * @return string
     */
    public function getMinPrice();

    /**
     * @param $price
     * @return string
     */
    public function setMinPrice($price);

    /**
     * @return string
     */
    public function getInstallmentsType();

    /**
     * @param $type
     * @return string
     */
    public function setInstallmentsType($type);

    /**
     * @return mixed
     */
    public function getInstallments();

    /**
     * @param $installments
     * @return string
     */
    public function setInstallments($installments);

    /**
     * @return mixed
     */
    public function getDefaultInstallment();

    /**
     * @param $installment
     * @return mixed
     */
    public function setDefaultInstallment($installment);
    /**
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
