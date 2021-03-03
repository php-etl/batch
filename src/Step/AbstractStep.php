<?php

namespace Kiboko\Component\Workflow\Step;

use Kiboko\Component\Workflow\Event\AfterStepExecutionEvent;
use Kiboko\Component\Workflow\Event\BeforeStepExecutionEvent;
use Kiboko\Component\Workflow\Event\EventInterface;
use Kiboko\Component\Workflow\Event\InvalidItemEvent;
use Kiboko\Component\Workflow\Event\StepExecutionErroredEvent;
use Kiboko\Component\Workflow\Event\StepExecutionEvent;
use Kiboko\Component\Workflow\Event\StepExecutionInterruptedEvent;
use Kiboko\Component\Workflow\Event\StepExecutionSuccessEvent;
use Kiboko\Component\Workflow\Item\InvalidItemInterface;
use Kiboko\Component\Workflow\Job\BatchStatus;
use Kiboko\Component\Workflow\Job\ExitStatus;
use Kiboko\Component\Workflow\Job\JobInterruptedException;
use Kiboko\Component\Workflow\Job\JobRepositoryInterface;
use Kiboko\Component\Workflow\Model\StepExecution;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A Step implementation that provides common behavior to subclasses, including registering and calling
 * listeners.
 *
 * Inspired by Spring Batch org.springframework.batch.core.step.AbstractStep;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractStep implements StepInterface
{
    /** @var string */
    protected $name;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface   $jobRepository
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository
    ) {
        $this->name = $name;
        $this->jobRepository = $jobRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return JobRepositoryInterface
     */
    public function getJobRepository()
    {
        return $this->jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * Do not catch exception here. It will be correctly handled by the execute() method.
     *
     * @param StepExecution $stepExecution the current step context
     *
     * @throws \Exception
     */
    abstract protected function doExecute(StepExecution $stepExecution);

    /**
     * Template method for step execution logic
     *
     * @param StepExecution $stepExecution
     *
     * @throws JobInterruptedException
     */
    final public function execute(StepExecution $stepExecution)
    {
        $this->eventDispatcher->dispatch(new BeforeStepExecutionEvent($stepExecution));

        $stepExecution->setStartTime(new \DateTime());
        $stepExecution->setStatus(new BatchStatus(BatchStatus::STARTED));
        $this->jobRepository->updateStepExecution($stepExecution);

        // Start with a default value that will be trumped by anything
        $exitStatus = new ExitStatus(ExitStatus::EXECUTING);

        try {
            $this->doExecute($stepExecution);

            $exitStatus = new ExitStatus(ExitStatus::COMPLETED);
            $exitStatus->logicalAnd($stepExecution->getExitStatus());

            $this->jobRepository->updateStepExecution($stepExecution);

            // Check if someone is trying to stop us
            if ($stepExecution->isTerminateOnly()) {
                throw new JobInterruptedException("JobExecution interrupted.");
            }

            // Need to upgrade here not set, in case the execution was stopped
            $stepExecution->upgradeStatus(BatchStatus::COMPLETED);
            $this->eventDispatcher->dispatch(new StepExecutionSuccessEvent($stepExecution));
        } catch (\Exception $e) {
            $stepExecution->upgradeStatus($this->determineBatchStatus($e));

            $exitStatus = $exitStatus->logicalAnd($this->getDefaultExitStatusForFailure($e));

            $stepExecution->addFailureException($e);
            $this->jobRepository->updateStepExecution($stepExecution);

            if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
                $this->eventDispatcher->dispatch(new StepExecutionInterruptedEvent($stepExecution));
            } else {
                $this->eventDispatcher->dispatch(new StepExecutionErroredEvent($stepExecution));
            }
        }

        $this->eventDispatcher->dispatch(new AfterStepExecutionEvent($stepExecution));

        $stepExecution->setEndTime(new \DateTime());
        $stepExecution->setExitStatus($exitStatus);
        $this->jobRepository->updateStepExecution($stepExecution);
    }

    /**
     * Determine the step status based on the exception.
     * @param \Exception $e
     *
     * @return int
     */
    private static function determineBatchStatus(\Exception $e)
    {
        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            return BatchStatus::STOPPED;
        } else {
            return BatchStatus::FAILED;
        }
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param \Exception $e the cause of the failure
     *
     * @return ExitStatus {@link ExitStatus}
     */
    private function getDefaultExitStatusForFailure(\Exception $e)
    {
        if ($e instanceof JobInterruptedException || $e->getPrevious() instanceof JobInterruptedException) {
            $exitStatus = new ExitStatus(ExitStatus::STOPPED);
            $exitStatus->addExitDescription(get_class(new JobInterruptedException()));
        } else {
            $exitStatus = new ExitStatus(ExitStatus::FAILED);
            $exitStatus->addExitDescription($e);
        }

        return $exitStatus;
    }
}
