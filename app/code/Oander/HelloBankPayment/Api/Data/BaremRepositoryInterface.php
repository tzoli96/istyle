<?php
namespace Oander\HelloBankPayment\Api\Data;

use Oander\HelloBankPayment\Api\Data\BaremInterface;

interface BaremRepositoryInterface
{

    /**
     * @return BaremInterface
     */
    public function create(): BaremInterface;


    /**
     * @param int $baremId
     *
     * @return BaremInterface
     */
    public function get(int $baremId): BaremInterface;

    /**
     * @param int $baremId
     *
     * @return mixed
     */
    public function getById(int $baremId);

    /**
     * @param int $baremId
     *
     * @return void
     */
    public function delete(int $baremId);


    /**
     * @param BaremInterface  $barem
     *
     * @return BaremInterface
     */
    public function save(BaremInterface $barem): BaremInterface;

    /**
     * @param int $baremId
     * @param int $status
     */
    public function updateStatus(int $baremId, int $status);


}