<?php declare(strict_types=1);

namespace Kiboko\Component\Workflow\Event;

use Kiboko\Component\Workflow\Model\StepExecution;
use Symfony\Contracts\EventDispatcher\Event;

final class StepExecutionSuccessEvent extends Event implements EventInterface
{
    /** @var StepExecution */
    private $stepExecution;

    public function __construct(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function getStepExecution(): StepExecution
    {
        return $this->stepExecution;
    }
}
