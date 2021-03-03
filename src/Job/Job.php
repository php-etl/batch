<?php

namespace Kiboko\Component\Workflow\Job;

use Kiboko\Component\Workflow\Event\AfterJobExecutionEvent;
use Kiboko\Component\Workflow\Event\BeforeJobExecutionEvent;
use Kiboko\Component\Workflow\Event\BeforeJobStatusUpgradeEvent;
use Kiboko\Component\Workflow\Event\JobExecutionFatalErrorEvent;
use Kiboko\Component\Workflow\Event\JobExecutionInterruptedEvent;
use Kiboko\Component\Workflow\Event\JobExecutionStoppedEvent;
use Kiboko\Component\Workflow\Model\JobExecutionInterface;
use Kiboko\Component\Workflow\Model\StepExecution;
use Kiboko\Component\Workflow\Step\StepInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Implementation of the {@link Job} interface.
 *
 * Inspired by Spring Batch org.springframework.batch.core.job.AbstractJob;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class Job implements JobInterface
{
    /** @var string */
    protected $name;

    /* @var EventDispatcherInterface */
    protected $eventDispatcher;

    /* @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var array */
    protected $steps;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param string                   $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface   $jobRepository
     * @param StepInterface[]          $steps
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        array $steps = []
    ) {
        $this->name = $name;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository = $jobRepository;
        $this->steps = $steps;
        $this->filesystem = new Filesystem();
    }

    /**
     * Get the job's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return all the steps
     *
     * @return array steps
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     *
     * @param string $stepName
     *
     * @return StepInterface the Step
     */
    public function getStep($stepName)
    {
        foreach ($this->steps as $step) {
            if ($step->getName() == $stepName) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Retrieve the step names.
     *
     * @return array the step names
     */
    public function getStepNames()
    {
        $names = [];
        foreach ($this->steps as $step) {
            $names[] = $step->getName();
        }

        return $names;
    }

    /**
     * Public getter for the {@link JobRepositoryInterface} that is needed to manage the
     * state of the batch meta domain (jobs, steps, executions) during the life
     * of a job.
     *
     * @return JobRepositoryInterface
     */
    public function getJobRepository()
    {
        return $this->jobRepository;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . ': [name=' . $this->name . ']';
    }

    /**
     * Run the specified job, handling all listener and repository calls, and
     * delegating the actual processing to {@link #doExecute(JobExecutionInterface)}.
     * @param JobExecutionInterface $jobExecution
     *
     * @see Job#execute(JobExecutionInterface)
     *
     * A unique working directory is created before the execution of the job. It is deleted when the job is terminated.
     * The working directory is created in the temporary filesystem. Its pathname is placed in the JobExecutionContext
     * via the key {@link \Kiboko\Component\Workflow\Job\JobInterface::WORKING_DIRECTORY_PARAMETER}
     */
    final public function execute(JobExecutionInterface $jobExecution)
    {
        try {
            $workingDirectory = $this->createWorkingDirectory();
            $jobExecution->getExecutionContext()->put(JobInterface::WORKING_DIRECTORY_PARAMETER, $workingDirectory);

            $this->eventDispatcher->dispatch(new BeforeJobExecutionEvent($jobExecution));

            if ($jobExecution->getStatus()->getValue() !== BatchStatus::STOPPING) {
                $jobExecution->setStartTime(new \DateTime());
                $this->updateStatus($jobExecution, BatchStatus::STARTED);
                $this->jobRepository->updateJobExecution($jobExecution);

                $this->doExecute($jobExecution);
            } else {
                // The job was already stopped before we even got this far. Deal
                // with it in the same way as any other interruption.
                $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPED));
                $jobExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));
                $this->jobRepository->updateJobExecution($jobExecution);

                $this->eventDispatcher->dispatch(new JobExecutionStoppedEvent($jobExecution));
            }

            if (($jobExecution->getStatus()->getValue() <= BatchStatus::STOPPED)
                && (count($jobExecution->getStepExecutions()) === 0)
            ) {
                $exitStatus = $jobExecution->getExitStatus();
                $noopExitStatus = new ExitStatus(ExitStatus::NOOP);
                $noopExitStatus->addExitDescription("All steps already completed or no steps configured for this job.");
                $jobExecution->setExitStatus($exitStatus->logicalAnd($noopExitStatus));
                $this->jobRepository->updateJobExecution($jobExecution);
            }

            $this->eventDispatcher->dispatch(new AfterJobExecutionEvent($jobExecution));

            $jobExecution->setEndTime(new \DateTime());
            $this->jobRepository->updateJobExecution($jobExecution);
        } catch (JobInterruptedException $e) {
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(
                new BatchStatus(
                    BatchStatus::max(BatchStatus::STOPPED, $e->getStatus()->getValue())
                )
            );
            $jobExecution->addFailureException($e);
            $this->jobRepository->updateJobExecution($jobExecution);

            $this->eventDispatcher->dispatch(new JobExecutionInterruptedEvent($jobExecution));
        } catch (\Throwable $e) {
            $jobExecution->setExitStatus($this->getDefaultExitStatusForFailure($e));
            $jobExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
            $jobExecution->addFailureException($e);
            $this->jobRepository->updateJobExecution($jobExecution);

            $this->eventDispatcher->dispatch(new JobExecutionFatalErrorEvent($jobExecution));
        } finally {
            $workingDirectory = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER);
            if (null !== $workingDirectory) {
                $this->deleteWorkingDirectory($workingDirectory);
            }
        }
    }

    /**
     * Handler of steps sequentially as provided, checking each one for success
     * before moving to the next. Returns the last {@link StepExecution}
     * successfully processed if it exists, and null if none were processed.
     *
     * @param JobExecutionInterface $jobExecution the current {@link JobExecutionInterface}
     *
     * @throws JobInterruptedException
     */
    protected function doExecute(JobExecutionInterface $jobExecution)
    {
        /* @var StepExecution $stepExecution */
        $stepExecution = null;

        foreach ($this->steps as $step) {
            $stepExecution = $this->handleStep($step, $jobExecution);
            $this->jobRepository->updateStepExecution($stepExecution);

            if ($stepExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
                // Terminate the job if a step fails
                break;
            }
        }

        // Update the job status to be the same as the last step
        if ($stepExecution !== null) {
            $this->eventDispatcher->dispatch(new BeforeJobStatusUpgradeEvent($jobExecution));

            $jobExecution->upgradeStatus($stepExecution->getStatus()->getValue());
            $jobExecution->setExitStatus($stepExecution->getExitStatus());
            $this->jobRepository->updateJobExecution($jobExecution);
        }
    }

    /**
     * Handle a step and return the execution for it.
     * @param StepInterface $step         Step
     * @param JobExecutionInterface  $jobExecution Job execution
     *
     * @throws JobInterruptedException
     *
     * @return StepExecution
     */
    protected function handleStep(StepInterface $step, JobExecutionInterface $jobExecution)
    {
        if ($jobExecution->isStopping()) {
            throw new JobInterruptedException("JobExecution interrupted.");
        }

        $stepExecution = $jobExecution->createStepExecution($step->getName());

        try {
            $step->execute($stepExecution);
        } catch (JobInterruptedException $e) {
            $stepExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            $this->jobRepository->updateStepExecution($stepExecution);
            throw $e;
        }

        if ($stepExecution->getStatus()->getValue() == BatchStatus::STOPPING
            || $stepExecution->getStatus()->getValue() == BatchStatus::STOPPED) {
            $jobExecution->setStatus(new BatchStatus(BatchStatus::STOPPING));
            $this->jobRepository->updateJobExecution($jobExecution);
            throw new JobInterruptedException("Job interrupted by step execution");
        }

        return $stepExecution;
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param \Exception $e the cause of the failure
     *
     * @return ExitStatus an {@link ExitStatus}
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

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param JobExecutionInterface $jobExecution Execution of the job
     * @param string       $status       Status of the execution
     *
     * @return an {@link ExitStatus}
     */
    private function updateStatus(JobExecutionInterface $jobExecution, $status)
    {
        $jobExecution->setStatus(new BatchStatus($status));
    }

    /**
     * Create a unique working directory
     *
     * @return string the working directory path
     */
    private function createWorkingDirectory()
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('akeneo_batch_') . DIRECTORY_SEPARATOR;
        try {
            $this->filesystem->mkdir($path);
        } catch (IOException $e) {
            // this exception will be catched by {Job->execute()} and will set the batch as failed
            throw new RuntimeErrorException(
                sprintf('Unable to create the working directory "%s".', $path),
                $e->getCode(),
                $e
            );
        }

        return $path;
    }

    /**
     * Delete the working directory
     *
     * @param string $directory
     */
    private function deleteWorkingDirectory($directory)
    {
        if ($this->filesystem->exists($directory)) {
            $this->filesystem->remove($directory);
        }
    }
}
