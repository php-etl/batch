<?php

namespace spec\Kiboko\Component\ETL\Batch\Model;

use Kiboko\Component\ETL\Batch\Model\JobExecutionInterface;
use Kiboko\Component\ETL\Batch\Model\JobInstanceInterface;
use PhpSpec\ObjectBehavior;

class JobInstanceSpec extends ObjectBehavior
{
    function it_is_properly_instanciated()
    {
        $this->beConstructedWith('connector', 'type', 'job_name');
        $this->getConnector()->shouldReturn('connector');
        $this->getType()->shouldReturn('type');
        $this->getJobName()->shouldReturn('job_name');
    }

    function it_is_cloneable(JobExecutionInterface $jobExecution)
    {
        $jobExecution
            ->addStepExecution($this->getWrappedObject())
            ->willReturn($jobExecution);

        $this->addJobExecution($jobExecution);
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf(JobInstanceInterface::class);
        $clone->getJobExecutions()->shouldHaveCount(1);
        $clone->getId()->shouldReturn(null);
    }

    function it_throws_logic_exception_when_changes_job_name()
    {
        $this->beConstructedWith('connector', 'type', 'old_job_name');
        $this->shouldThrow(
            new \LogicException('Job name already set in JobInstance')
        )->during(
            'setJobName',
            ['new_job_name']
        );
    }

    function it_throws_logic_exception_when_changes_connector()
    {
        $this->beConstructedWith('oldconnector', 'type', 'job_name');
        $this->shouldThrow(
            new \LogicException('Connector already set in JobInstance')
        )->during(
            'setConnector',
            ['newconnector']
        );
    }
}
