<?php
namespace Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler;

use Aheadworks\Popup\Model\ResourceModel\EntityRelation\HandlerInterface;

/**
 * Class Composite
 * @package Aheadworks\Popup\Model\ResourceModel\EntityRelation\Handler
 */
class Composite implements HandlerInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @param array $handlers
     */
    public function __construct(
        array $handlers = []
    ) {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave($entity)
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof HandlerInterface) {
                $handler->afterSave($entity);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterLoad($entity)
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof HandlerInterface) {
                $handler->afterLoad($entity);
            }
        }
    }
}
