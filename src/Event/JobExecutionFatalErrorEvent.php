<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\Batch\Event;

use Kiboko\Component\ETL\Batch\Model\JobExecutionInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class JobExecutionFatalErrorEvent extends Event implements EventInterface
{
    /** @var JobExecutionInterface */
    protected $jobExecution;

    public function __construct(JobExecutionInterface $jobExecution)
    {
        $this->jobExecution = $jobExecution;
    }

    public function getJobExecution(): JobExecutionInterface
    {
        return $this->jobExecution;
    }
}