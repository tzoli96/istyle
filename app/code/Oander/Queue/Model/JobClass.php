<?php


namespace Oander\Queue\Model;

abstract class JobClass extends \Magento\Framework\DataObject
{
    const NAME_SEPARATOR = "_";

    protected $hasError = false;
    protected $input = null;
    protected $output = null;

    /**
     * Clear Object data
     * @return void
     */
    public function clear()
    {
        $this->hasError = false;
        $this->input = null;
        $this->output = null;
        $this->setData([]);
    }

    /**
     * Callback function when execution needed
     *
     * @return bool
     */
    abstract public function execute(): bool;

    /**
     * Get Name
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get Retries count for this class
     * @return int
     */
    abstract public function getRetriesCount(): int;

    /**
     * Executed with error, job will be finished
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * Get Input as array or null if no input was created
     * @return string|null
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Get Output as array or null if no input was created
     * @return string|null
     */
    public function getOutput()
    {
        return $this->output;
    }
}
