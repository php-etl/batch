<?php

namespace Kiboko\Component\ETL\Batch\Model;

use Kiboko\Component\ETL\Batch\Item\ExecutionContext;
use Kiboko\Component\ETL\Batch\Item\InvalidItemInterface;
use Kiboko\Component\ETL\Batch\Job\BatchStatus;
use Kiboko\Component\ETL\Batch\Job\ExitStatus;
use Kiboko\Component\ETL\Batch\Job\JobParameters;
use Kiboko\Component\ETL\Batch\Job\RuntimeErrorException;

/**
 * Batch domain object representation the execution of a step. Unlike JobExecutionInterface, there are additional properties
 * related the processing of items such as commit count, etc.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class StepExecution
{
    /** @var integer */
    private $id;

    /** @var JobExecutionInterface */
    private $jobExecution = null;

    /** @var string */
    private $stepName;

    /** @var integer */
    private $status = null;

    /** @var integer */
    private $readCount = 0;

    /** @var integer */
    private $writeCount = 0;

    /** @var integer */
    private $filterCount = 0;

    /** @var \DateTime */
    private $startTime;

    /** @var \DateTime */
    private $endTime;

    /* @var ExecutionContext $executionContext */
    private $executionContext;

    /* @var ExitStatus */
    private $exitStatus = null;

    /** @var string */
    private $exitCode = null;

    /** @var string */
    private $exitDescription = null;

    /** @var boolean */
    private $terminateOnly = false;

    /** @var array */
    private $failureExceptions = null;

    /** @var array */
    private $errors = [];

    /** @var iterable */
    private $warnings;

    /** @var array */
    private $summary = [];

    /**
     * Constructor with mandatory properties.
     *
     * @param string                $stepName     the step to which this execution belongs
     * @param JobExecutionInterface $jobExecution the current job execution
     */
    public function __construct(string $stepName, JobExecutionInterface $jobExecution)
    {
        $this->stepName = $stepName;
        $this->jobExecution = $jobExecution;
        $jobExecution->addStepExecution($this);
        $this->warnings = [];
        $this->executionContext = new ExecutionContext();
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::EXECUTING));

        $this->failureExceptions = [];
        $this->errors = [];

        $this->startTime = new \DateTime();
    }

    /**
     * Reset id on clone
     */
    public function __clone()
    {
        $this->id = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the {@link ExecutionContext} for this execution
     *
     * @return ExecutionContext with its attributes
     */
    public function getExecutionContext(): ExecutionContext
    {
        return $this->executionContext;
    }

    /**
     * Sets the {@link ExecutionContext} for this execution
     *
     * @param ExecutionContext $executionContext the attributes
     */
    public function setExecutionContext(ExecutionContext $executionContext): StepExecution
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return \DateTimeInterface time that this execution ended
     */
    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param \DateTimeInterface $endTime the time that this execution ended
     *
     * @return StepExecution
     */
    public function setEndTime(\DateTimeInterface $endTime): StepExecution
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Returns the current number of items read for this execution
     *
     * @return int the current number of items read for this execution
     */
    public function getReadCount(): int
    {
        return $this->readCount;
    }

    /**
     * Sets the current number of read items for this execution
     *
     * @param int $readCount the current number of read items for this execution
     *
     * @return StepExecution
     */
    public function setReadCount(int $readCount): StepExecution
    {
        $this->readCount = $readCount;

        return $this;
    }

    /** Increment the read count by 1 */
    public function incrementReadCount(int $step = 1): void
    {
        $this->readCount += max(1, $step);
    }

    /**
     * Returns the current number of items written for this execution
     *
     * @return int the current number of items written for this execution
     */
    public function getWriteCount(): int
    {
        return $this->writeCount;
    }

    /**
     * Sets the current number of written items for this execution
     *
     * @param int $writeCount the current number of written items for this execution
     */
    public function setWriteCount(int $writeCount): StepExecution
    {
        $this->writeCount = $writeCount;

        return $this;
    }

    /** Increment the write count by 1 */
    public function incrementWriteCount(int $step = 1): void
    {
        $this->writeCount += max(1, $step);
    }

    /**
     * Returns the current number of items filtered out of this execution
     *
     * @return int the current number of items filtered out of this execution
     */
    public function getFilterCount(): int
    {
        return $this->readCount - $this->writeCount;
    }

    /**
     * @return bool flag to indicate that an execution should halt
     */
    public function isTerminateOnly(): bool
    {
        return $this->terminateOnly;
    }

    /**
     * Set a flag that will signal to an execution environment that this
     * execution (and its surrounding job) wishes to exit.
     *
     * @return StepExecution
     */
    public function setTerminateOnly(): StepExecution
    {
        $this->terminateOnly = true;

        return $this;
    }

    /**
     * Gets the time this execution started
     *
     * @return \DateTimeInterface The time this execution started
     */
    public function getStartTime(): \DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * Sets the time this execution started
     *
     * @param \DateTimeInterface $startTime the time this execution started
     *
     * @return StepExecution
     */
    public function setStartTime(\DateTimeInterface $startTime): StepExecution
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus(): BatchStatus
    {
        return new BatchStatus($this->status);
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     */
    public function setStatus(BatchStatus $status): StepExecution
    {
        $this->status = $status->getValue();

        return $this;
    }

    /**
     * Upgrade the status field if the provided value is greater than the
     * existing one. Clients using this method to set the status can be sure
     * that they don't overwrite a failed status with an successful one.
     *
     * @param mixed $status the new status value
     */
    public function upgradeStatus($status): StepExecution
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @return string the name of the step
     */
    public function getStepName(): string
    {
        return $this->stepName;
    }

    /**
     * @param ExitStatus $exitStatus
     */
    public function setExitStatus(ExitStatus $exitStatus): StepExecution
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
    }

    /**
     * @return ExitStatus the exit status
     */
    public function getExitStatus(): ExitStatus
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the execution context information of the enclosing job.
     *
     * @return JobExecutionInterface the job execution that was used to start this step execution.
     *
     */
    public function getJobExecution(): JobExecutionInterface
    {
        return $this->jobExecution;
    }

    /**
     * Accessor for the job parameters
     */
    public function getJobParameters(): JobParameters
    {
        return $this->jobExecution->getJobParameters();
    }

    /**
     * Get failure exceptions
     *
     * @return iterable|\Throwable[]
     */
    public function getFailureExceptions(): iterable
    {
        return $this->failureExceptions;
    }

    /**
     * Add a failure exception
     */
    public function addFailureException(\Throwable $e): StepExecution
    {
        $this->failureExceptions[] = [
            'class'             => get_class($e),
            'message'           => $e->getMessage(),
            'messageParameters' => $e instanceof RuntimeErrorException ? $e->getMessageParameters() : [],
            'code'              => $e->getCode(),
            'trace'             => $e->getTraceAsString()
        ];

        return $this;
    }

    public function getFailureExceptionMessages(): string
    {
        return implode(
            ' ',
            array_map(
                function ($e) {
                    return $e['message'];
                },
                $this->failureExceptions
            )
        );
    }

    public function addError(string $message): StepExecution
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors(): iterable
    {
        return $this->errors;
    }

    /** Add a warning */
    public function addWarning(string $reason, array $reasonParameters, InvalidItemInterface $item): void
    {
        $data = $item->getInvalidData();

        if (null === $data) {
            $data = [];
        }

        if (is_object($data)) {
            $data = [
                'class'  => get_class($data),
                'id'     => method_exists($data, 'getId') ? $data->getId() : '[unknown]',
                'string' => method_exists($data, '__toString') ? (string) $data : '[unknown]',
            ];
        }

        $this->warnings->add(
            new Warning(
                $this,
                $reason,
                $reasonParameters,
                $data
            )
        );
    }

    /** Get the warnings */
    public function getWarnings(): iterable
    {
        return $this->warnings;
    }

    /**
     * Add row in summary
     *
     * @param mixed $info
     */
    public function addSummaryInfo(string $key, $info)
    {
        $this->summary[$key] = $info;
    }

    /** Increment counter in summary */
    public function incrementSummaryInfo(string $key, int $step = 1)
    {
        if (!isset($this->summary[$key])) {
            $this->summary[$key] = max(1, $step);
        } else {
            $this->summary[$key] += max(1, $step);
        }
    }

    /**
     * Get a summary row
     *
     * @return mixed
     */
    public function getSummaryInfo(string $key)
    {
        return $this->summary[$key];
    }

    /**
     * Set summary
     *
     * @param array $summary
     */
    public function setSummary(array $summary): StepExecution
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return array
     */
    public function getSummary(): array
    {
        return $this->summary;
    }

    /**
     * To string
     */
    public function __toString()
    {
        $summary = 'id=%d, name=[%s], status=[%s], exitCode=[%s], exitDescription=[%s]';

        return sprintf(
            $summary,
            $this->id,
            $this->stepName,
            $this->status,
            $this->exitCode,
            $this->exitDescription
        );
    }
}
