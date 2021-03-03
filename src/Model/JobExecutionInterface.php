<?php

namespace Kiboko\Component\Workflow\Model;


use Kiboko\Component\Workflow\Item\ExecutionContext;
use Kiboko\Component\Workflow\Job\BatchStatus;
use Kiboko\Component\Workflow\Job\ExitStatus;
use Kiboko\Component\Workflow\Job\JobParameters;

/**
 * Batch domain object representing the execution of a job
 *
 * Inspired by Spring Batch  org.springframework.batch.job.JobExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobExecutionInterface
{
    /**
     * Returns the {@link ExecutionContext} for this execution
     *
     * @return ExecutionContext with its attributes
     */
    public function getExecutionContext(): ?ExecutionContext;

    /**
     * Sets the {@link ExecutionContext} for this execution
     *
     * @param ExecutionContext $executionContext the attributes
     */
    public function setExecutionContext(ExecutionContext $executionContext): JobExecutionInterface;

    /**
     * Returns the time that this execution ended
     *
     * @return \DateTimeInterface the time that this execution ended
     */
    public function getEndTime(): ?\DateTimeInterface;

    /**
     * Sets the time that this execution ended
     *
     * @param \DateTimeInterface $endTime the time that this execution ended
     */
    public function setEndTime(\DateTimeInterface $endTime): JobExecutionInterface;

    /**
     * Gets the time this execution started
     *
     * @return \DateTimeInterface the time this execution started
     */
    public function getStartTime(): ?\DateTimeInterface;

    /**
     * Sets the time this execution started
     *
     * @param \DateTimeInterface $startTime the time this execution started
     */
    public function setStartTime(\DateTimeInterface $startTime): JobExecutionInterface;

    /**
     * Gets the time this execution has been created
     *
     * @return \DateTimeInterface the time this execution has been created
     */
    public function getCreateTime(): ?\DateTimeInterface;

    /**
     * Sets the time this execution has been created
     *
     * @param \DateTimeInterface $createTime the time this execution has been created
     */
    public function setCreateTime(\DateTimeInterface $createTime): JobExecutionInterface;

    /**
     * Gets the time this execution has been updated
     *
     * @return \DateTimeInterface time this execution has been updated
     */
    public function getUpdatedTime(): ?\DateTimeInterface;

    /**
     * Sets the time this execution has been updated
     *
     * @param \DateTimeInterface $updatedTime the time this execution has been updated
     */
    public function setUpdatedTime(\DateTimeInterface $updatedTime): JobExecutionInterface;

    /**
     * Returns the process identifier of the batch job
     *
     * @return int|null
     */
    public function getPid(): ?int;

    /** Sets the process identifier of the batch job*/
    public function setPid(int $pid): JobExecutionInterface;

    /** Returns the user who launched the job*/
    public function getUser(): ?string;

    /** Sets the user who launched the job*/
    public function setUser(string $user): JobExecutionInterface;

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus(): BatchStatus;

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     */
    public function setStatus(BatchStatus $status): JobExecutionInterface;

    /**
     * Upgrade the status field if the provided value is greater than the
     * existing one. Clients using this method to set the status can be sure
     * that they don't overwrite a failed status with an successful one.
     *
     * @param mixed $status the new status value
     */
    public function upgradeStatus($status): JobExecutionInterface;

    public function setExitStatus(ExitStatus $exitStatus): JobExecutionInterface;

    public function getExitStatus(): ExitStatus;

    /**
     * Accessor for the step executions.
     *
     * @return StepExecution[] the step executions that were registered
     */
    public function getStepExecutions(): iterable;

    /**
     * Register a step execution with the current job execution.
     *
     * @param mixed $stepName the name of the step the new execution is associated with
     *
     * @return StepExecution the created stepExecution
     */
    public function createStepExecution(string $stepName): StepExecution;

    /**
     * Add a step executions to job's step execution
     *
     * @param StepExecution $stepExecution
     */
    public function addStepExecution(StepExecution $stepExecution): JobExecutionInterface;

    /**
     * Test if this JobExecutionInterface indicates that it is running. It should
     * be noted that this does not necessarily mean that it has been persisted
     * as such yet.
     *
     * @return bool if the end time is null
     */
    public function isRunning(): bool;

    /**
     * Test if this JobExecutionInterface indicates that it has been signalled to
     * stop.
     * @return bool if the status is BatchStatus::STOPPING
     */
    public function isStopping(): bool;

    /**
     * Signal the JobExecutionInterface to stop. Iterates through the associated
     * StepExecution, calling StepExecution::setTerminateOnly().
     */
    public function stop(): JobExecutionInterface;

    /**
     * Get failure exceptions
     *
     * @return \Throwable[]
     */
    public function getFailureExceptions(): iterable;

    /** Add a failure exception*/
    public function addFailureException(\Throwable $e): JobExecutionInterface;

    /**
     * Return all failure causing exceptions for this JobExecutionInterface, including
     * step executions.
     *
     * @return iterable|\Throwable[] containing all exceptions causing failure for this JobExecutionInterface.
     */
    public function getAllFailureExceptions(): iterable;

    /**
     * Set the associated job
     *
     * @param JobInstanceInterface $jobInstance The job instance to associate the JobExecutionInterface to
     */
    public function setJobInstance(JobInstanceInterface $jobInstance): JobExecutionInterface;

    /**
     * Get the associated jobInstance
     *
     * @return JobInstanceInterface The job to which the JobExecutionInterface is associated
     */
    public function getJobInstance(): JobInstanceInterface;

    /** Get the associated jobInstance label */
    public function getLabel(): string;

    /** Set the log file */
    public function setLogFile(string $logFile): JobExecutionInterface;

    /** Get the log file */
    public function getLogFile(): string;

    /** Format a date or return empty string if null */
    public static function formatDate(\DateTimeInterface $date = null, string $format = \DateTime::ATOM): string;

    public function setJobParameters(JobParameters $jobParameters): void;

    public function getJobParameters(): JobParameters;
}
