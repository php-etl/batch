<?php

namespace spec\Kiboko\Component\Workflow\Event;

use Kiboko\Component\Workflow\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class StepExecutionInterruptedEventSpec extends ObjectBehavior
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
