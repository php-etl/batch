<?php

namespace Kiboko\Component\Workflow\Model;


/**
 * Batch domain object representing a uniquely identifiable configured job.
 *
 * Cf https://docs.spring.io/spring-batch/apidocs/org/springframework/batch/core/JobInstance.html
 *
 * Please note the following difference between Spring Batch and Akeneo Batch,
 *
 * In Spring Batch: a JobInstance can be restarted multiple times in case of execution failure and it's lifecycle ends
 * with first successful execution. Trying to execute an existing JobInstance that has already completed successfully
 * will result in error. Error will be raised also for an attempt to restart a failed JobInstance if the Job is not restartable.
 *
 * In Akeneo Batch: the behavior is not the same, we store a JobInstance, we can run the Job then run it again with the
 * same config, change the config, then run it again.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobInstanceInterface
{
    /** Get id */
    public function getId(): ?int;

    /** Set code */
    public function setCode(string $code): JobInstanceInterface;

    /** Get code */
    public function getCode(): string;

    /** Set label */
    public function setLabel(string $label): JobInstanceInterface;

    /** Get label */
    public function getLabel(): string;

    /** Get connector */
    public function getConnector(): string;

    /** Get job name */
    public function getJobName(): string;

    /** Get status */
    public function getStatus(): int;

    /** Set status */
    public function setStatus(int $status): JobInstanceInterface;

    /** Set type */
    public function setType(string $type): JobInstanceInterface;

    /** Get type */
    public function getType(): string;

    /** This parameters can be used to create a JobParameters, stored like this in a legacy way */
    public function setRawParameters(array $rawParameters): JobInstanceInterface;

    /** This parameters can be used to create a JobParameters, stored like this in a legacy way */
    public function getRawParameters(): array;

    /**
     * @return iterable|JobExecutionInterface[]
     */
    public function getJobExecutions(): iterable;

    public function addJobExecution(JobExecutionInterface $jobExecution): JobInstanceInterface;

    public function removeJobExecution(JobExecutionInterface $jobExecution): JobInstanceInterface;

    /**
     * Set job name
     *
     * Throws logic exception if job name property is already set.
     *
     * @throws \LogicException
     */
    public function setJobName(string $jobName): JobInstanceInterface;

    /**
     * Set connector
     * Throws exception if connector property is already set.
     *
     * @throws \LogicException
     */
    public function setConnector(string $connector): JobInstanceInterface;
}
