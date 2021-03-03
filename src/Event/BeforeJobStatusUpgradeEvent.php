<?php declare(strict_types=1);

namespace Kiboko\Component\Workflow\Event;

use Kiboko\Component\Workflow\Model\JobExecutionInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeJobStatusUpgradeEvent extends Event implements EventInterface
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
