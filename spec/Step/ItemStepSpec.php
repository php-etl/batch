<?php

namespace spec\Kiboko\Component\Workflow\Step;

use Kiboko\Component\Workflow\Event\AfterStepExecutionEvent;
use Kiboko\Component\Workflow\Event\BeforeStepExecutionEvent;
use Kiboko\Component\Workflow\Event\EventInterface;
use Kiboko\Component\Workflow\Event\StepExecutionSuccessEvent;
use Kiboko\Component\Workflow\Item\FileInvalidItem;
use Kiboko\Component\Workflow\Item\InvalidItemException;
use Kiboko\Component\Workflow\Item\ItemProcessorInterface;
use Kiboko\Component\Workflow\Item\ItemReaderInterface;
use Kiboko\Component\Workflow\Item\ItemWriterInterface;
use Kiboko\Component\Workflow\Job\BatchStatus;
use Kiboko\Component\Workflow\Job\ExitStatus;
use Kiboko\Component\Workflow\Job\JobRepositoryInterface;
use Kiboko\Component\Workflow\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ItemStepSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $dispatcher,
        JobRepositoryInterface $repository,
        ItemReaderInterface $reader,
        ItemProcessorInterface $processor,
        ItemWriterInterface $writer
    ) {
        $this->beConstructedWith('myname', $dispatcher, $repository, $reader, $processor, $writer, 3);
    }

    function it_executes_with_success(
        $reader,
        $processor,
        $writer,
        StepExecution $execution,
        $dispatcher,
        $repository,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

        $dispatcher->dispatch(Argument::type(BeforeStepExecutionEvent::class))->shouldBeCalled();
        $execution->setStartTime(Argument::any())->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();

        // second batch
        $processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldBeCalled();

        $execution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);
        $repository->updateStepExecution($execution)->shouldBeCalled();
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::type(StepExecutionSuccessEvent::class))->shouldBeCalled();
        $dispatcher->dispatch(Argument::type(AfterStepExecutionEvent::class))->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }

    function it_executes_with_an_invalid_item_during_processing(
        $reader,
        $processor,
        $writer,
        StepExecution $execution,
        $dispatcher,
        $repository,
        BatchStatus $status,
        ExitStatus $exitStatus
    ) {
        $execution->getStatus()->willReturn($status);
        $status->getValue()->willReturn(BatchStatus::STARTING);

        $dispatcher->dispatch(Argument::type(BeforeStepExecutionEvent::class))->shouldBeCalled();
        $execution->setStartTime(Argument::any())->shouldBeCalled();
        $execution->setStatus(Argument::any())->shouldBeCalled();

        // first batch
        $reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $processor->process('r1')->shouldBeCalled()->willReturn('p1');
        $processor->process('r2')->shouldBeCalled()->willReturn('p2');
        $processor->process('r3')->shouldBeCalled()->willReturn('p3');
        $writer->write(['p1', 'p2', 'p3'])->shouldBeCalled();

        // second batch
        $processor->process('r4')->shouldBeCalled()->willThrow(
            new InvalidItemException('my msg', new FileInvalidItem(['r4'], 7))
        );
        $execution->addWarning(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $processor->process(null)->shouldNotBeCalled();
        $writer->write(['p4'])->shouldNotBeCalled();

        $execution->getExitStatus()->willReturn($exitStatus);
        $exitStatus->getExitCode()->willReturn(ExitStatus::COMPLETED);
        $repository->updateStepExecution($execution)->shouldBeCalled();
        $execution->isTerminateOnly()->willReturn(false);

        $execution->upgradeStatus(Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(Argument::type(StepExecutionSuccessEvent::class))->shouldBeCalled();
        $dispatcher->dispatch(Argument::type(AfterStepExecutionEvent::class))->shouldBeCalled();
        $execution->setEndTime(Argument::any())->shouldBeCalled();
        $execution->setExitStatus(Argument::any())->shouldBeCalled();

        $this->execute($execution);
    }
}
