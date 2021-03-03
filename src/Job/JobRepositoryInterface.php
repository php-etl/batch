<?php

namespace Kiboko\Component\Workflow\Job;

use Kiboko\Component\Workflow\Model\JobExecutionInterface;
use Kiboko\Component\Workflow\Model\JobInstanceInterface;
use Kiboko\Component\Workflow\Model\StepExecution;

/**
 * Common interface for Job repositories which should handle how job are stored, updated
 * and retrieved
 *
 * Inspired by Spring Batch org.springframework.batch.core.repository.JobRepository;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobRepositoryInterface
{
    /**
     * Create a JobExecutionInterface object
     *
     * @param JobInstanceInterface $job
     * @param JobParameters $jobParameters
     *
     * @return JobExecutionInterface
     */
    public function createJobExecution(JobInstanceInterface $job, JobParameters $jobParameters);

    /**
     * Update a JobExecutionInterface
     *
     * @param JobExecutionInterface $jobExecution
     *
     * @return JobExecutionInterface
     */
    public function updateJobExecution(JobExecutionInterface $jobExecution);

    /**
     * Update a StepExecution
     *
     * @param StepExecution $stepExecution
     *
     * @return StepExecution
     */
    public function updateStepExecution(StepExecution $stepExecution);

    /**
     * Get the last job execution
     *
     * @param JobInstanceInterface $jobInstance
     * @param int $status
     *
     * @return JobExecutionInterface|null
     */
    public function getLastJobExecution(JobInstanceInterface $jobInstance, $status);

    /**
     * Get purgeables jobs executions
     *
     * @param integer $days
     *
     * @return array
     */
    public function findPurgeables($days);

    /**
     * Remove jobs executions
     *
     * @param array $jobsExecutions
     */
    public function remove(array $jobsExecutions);
}
