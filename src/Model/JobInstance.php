<?php

namespace Kiboko\Component\ETL\Batch\Model;

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
class JobInstance implements JobInstanceInterface
{
    const STATUS_READY       = 0;
    const STATUS_DRAFT       = 1;
    const STATUS_IN_PROGRESS = 2;

    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    /** @var integer */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $jobName;

    /** @var integer */
    protected $status = self::STATUS_READY;

    /** @var string */
    protected $connector;

    /**
     * JobInstance type export or import
     *
     * @var string
     */
    protected $type;

    /** @var array */
    protected $rawParameters = [];

    /** @var iterable|JobExecutionInterface[] */
    protected $jobExecutions;

    /**
     * Constructor
     *
     * @param string $connector
     * @param string $type
     * @param string $jobName
     */
    public function __construct(string $connector = null, string $type = null, string $jobName = null)
    {
        $this->connector     = $connector;
        $this->type          = $type;
        $this->jobName       = $jobName;
        $this->jobExecutions = [];
    }

    /**
     * Reset id and clone job executions
     */
    public function __clone()
    {
        $this->id = null;

        if ($this->jobExecutions) {
            $this->jobExecutions = clone $this->jobExecutions;
        }
    }

    /** Get id */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** Set code */
    public function setCode(string $code): JobInstanceInterface
    {
        $this->code = $code;

        return $this;
    }

    /** Get code */
    public function getCode(): string
    {
        return $this->code;
    }

    /** Set label */
    public function setLabel(string $label): JobInstanceInterface
    {
        $this->label = $label;

        return $this;
    }

    /** Get label */
    public function getLabel(): string
    {
        return $this->label;
    }

    /** Get connector */
    public function getConnector(): string
    {
        return $this->connector;
    }

    /** Get job name */
    public function getJobName(): string
    {
        return $this->jobName;
    }

    /** Get status */
    public function getStatus(): int
    {
        return $this->status;
    }

    /** Set status */
    public function setStatus(int $status): JobInstanceInterface
    {
        $this->status = $status;

        return $this;
    }

    /** Set type */
    public function setType(string $type): JobInstanceInterface
    {
        $this->type = $type;

        return $this;
    }

    /** Get type */
    public function getType(): string
    {
        return $this->type;
    }

    /** This parameters can be used to create a JobParameters, stored like this in a legacy way */
    public function setRawParameters(array $rawParameters): JobInstanceInterface
    {
        $this->rawParameters = $rawParameters;

        return $this;
    }

    /** This parameters can be used to create a JobParameters, stored like this in a legacy way */
    public function getRawParameters(): array
    {
        return $this->rawParameters;
    }

    /**
     * @return iterable|JobExecutionInterface[]
     */
    public function getJobExecutions(): iterable
    {
        return $this->jobExecutions;
    }

    public function addJobExecution(JobExecutionInterface $jobExecution): JobInstanceInterface
    {
        $this->jobExecutions[] = $jobExecution;

        return $this;
    }

    public function removeJobExecution(JobExecutionInterface $jobExecution): JobInstanceInterface
    {
        $key = array_search($jobExecution, $this->jobExecutions, true);
        if ($key === false) {
            return $this;
        }

        unset($this->jobExecutions[$key]);

        return $this;
    }

    /**
     * Set job name
     *
     * Throws logic exception if job name property is already set.
     *
     * @throws \LogicException
     */
    public function setJobName(string $jobName): JobInstanceInterface
    {
        if ($this->jobName !== null) {
            throw new \LogicException('Job name already set in JobInstance');
        }

        $this->jobName = $jobName;

        return $this;
    }

    /**
     * Set connector
     * Throws exception if connector property is already set.
     *
     * @throws \LogicException
     */
    public function setConnector(string $connector): JobInstanceInterface
    {
        if ($this->connector !== null) {
            throw new \LogicException('Connector already set in JobInstance');
        }

        $this->connector = $connector;

        return $this;
    }
}
