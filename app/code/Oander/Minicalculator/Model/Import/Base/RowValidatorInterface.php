<?php

namespace Oander\Minicalculator\Model\Import\Base;

use Magento\Framework\Validator\ValidatorInterface;

interface RowValidatorInterface extends ValidatorInterface
{
    const ERROR_EMPTY = 'Empty %s column.';
    const ERROR_MISSING = 'Missing %s column.';
    const ERROR_STORE_NOT_EXIST = '%s store not exist.';
    const ERROR_BAREM_NOT_EXIST = '%s barem not exist.';

    /**
     * @param $context
     *
     * @return $this
     */
    public function init($context);
}
