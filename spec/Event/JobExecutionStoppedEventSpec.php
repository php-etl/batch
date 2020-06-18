<?php

namespace spec\Kiboko\Component\ETL\Batch\Event;

use Kiboko\Component\ETL\Batch\Model\JobExecutionInterface;
use PhpSpec\ObjectBehavior;

class JobExecutionStoppedEventSpec extends ObjectBehavior
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
