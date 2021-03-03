<?php

namespace spec\Kiboko\Component\Workflow\Model;

use Kiboko\Component\Workflow\Item\ExecutionContext;
use Kiboko\Component\Workflow\Item\InvalidItemInterface;
use Kiboko\Component\Workflow\Job\BatchStatus;
use Kiboko\Component\Workflow\Job\ExitStatus;
use Kiboko\Component\Workflow\Model\JobExecutionInterface;
use Kiboko\Component\Workflow\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StepExecutionSpec extends ObjectBehavior
{
    function let(JobExecutionInterface $jobExecution)
    {
        $this->beConstructedWith('myStepName', $jobExecution);

        $jobExecution
            ->addStepExecution($this->getWrappedObject())
            ->willReturn($jobExecution);
    }

    function it_is_properly_instanciated()
    {
        $this->getStatus()->shouldBeAnInstanceOf(BatchStatus::class);
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STARTING);
        $this->getExitStatus()->shouldBeAnInstanceOf(ExitStatus::class);
        $this->getExitStatus()->getExitCode()->shouldReturn(ExitStatus::EXECUTING);
        $this->getExecutionContext()->shouldBeAnInstanceOf(ExecutionContext::class);
        $this->getWarnings()->shouldHaveType('iterable');
        $this->getWarnings()->shouldBeEmpty();
        $this->getStartTime()->shouldBeAnInstanceOf('\Datetime');
        $this->getFailureExceptions()->shouldHaveCount(0);
    }

    function it_is_cloneable()
    {
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf(StepExecution::class);
        $clone->getId()->shouldReturn(null);
    }

    function it_upgrades_status()
    {
        $this->getStatus()->shouldBeAnInstanceOf(BatchStatus::class);
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STARTING);
        $this->upgradeStatus(BatchStatus::COMPLETED)->shouldBeAnInstanceOf(StepExecution::class);
        $this->getStatus()->shouldBeAnInstanceOf(BatchStatus::class);
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::COMPLETED);
    }

    function it_sets_exist_status(ExitStatus $exitStatus)
    {
        $this->setExitStatus($exitStatus)->shouldReturn($this);
    }

    function it_adds_a_failure_exception()
    {
        $exception = new \Exception('my msg');
        $this->addFailureException($exception)->shouldReturn($this);
        $this->getFailureExceptions()->shouldHaveCount(1);
    }

    function it_adds_warning(InvalidItemInterface $invalidItem)
    {
        $this->addWarning(
            'my reason',
            [],
            $invalidItem
        );
        $this->getWarnings()->shouldHaveCount(1);
    }

    function it_increments_summary_info()
    {
        $this->incrementSummaryInfo('counter');
        $this->getSummaryInfo('counter')->shouldReturn(1);
        $this->incrementSummaryInfo('counter', 3);
        $this->getSummaryInfo('counter')->shouldReturn(4);
    }

    function it_is_displayable()
    {
        $this->__toString()->shouldReturn('id=0, name=[myStepName], status=[2], exitCode=[EXECUTING], exitDescription=[]');
    }
}
