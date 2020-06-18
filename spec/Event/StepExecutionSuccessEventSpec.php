<?php

namespace spec\Kiboko\Component\ETL\Batch\Event;

use Kiboko\Component\ETL\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class StepExecutionSuccessEventSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->beConstructedWith($stepExecution);
    }

    function it_provides_the_step_execution($stepExecution)
    {
        $this->getStepExecution()->shouldReturn($stepExecution);
    }
}
