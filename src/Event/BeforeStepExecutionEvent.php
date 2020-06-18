<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Batch\Event;

use Kiboko\Component\ETL\Batch\Model\StepExecution;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeStepExecutionEvent extends Event implements EventInterface
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