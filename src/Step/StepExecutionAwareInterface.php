<?php

namespace Kiboko\Component\Workflow\Step;

use Kiboko\Component\Workflow\Model\StepExecution;

/**
 * Interface is used to receive StepExecution instance inside reader, processor or writer
 */
interface StepExecutionAwareInterface
{
    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution);
}
