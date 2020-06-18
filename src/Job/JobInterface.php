<?php

namespace Kiboko\Component\ETL\Batch\Job;

use Kiboko\Component\ETL\Batch\Model\JobExecutionInterface;

/**
 * Batch domain object representing a job. Job is an explicit abstraction
 * representing the configuration of a job specified by a developer.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.Job;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface JobInterface
{
    const WORKING_DIRECTORY_PARAMETER = 'working_directory';

    /**
     * @return string the name of this job
     */
    public function getName();

    /**
     * Run the {@link JobExecutionInterface} and update the meta information like status
     * and statistics as necessary. This method should not throw any exceptions
     * for failed execution. Clients should be careful to inspect the
     * {@link JobExecutionInterface} status to determine success or failure.
     *
     * @param JobExecutionInterface $execution a {@link JobExecutionInterface}
     */
    public function execute(JobExecutionInterface $execution);
}
