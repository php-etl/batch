<?php

namespace spec\Kiboko\Component\Workflow\Event;

use Kiboko\Component\Workflow\Model\JobExecutionInterface;
use PhpSpec\ObjectBehavior;

class JobExecutionFatalErrorEventSpec extends ObjectBehavior
{
    function let(JobExecutionInterface $jobExecution)
    {
        $this->beConstructedWith($jobExecution);
    }

    function it_provides_the_job_execution($jobExecution)
    {
        $this->getJobExecution()->shouldReturn($jobExecution);
    }
}
