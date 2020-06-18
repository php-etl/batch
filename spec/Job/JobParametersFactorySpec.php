<?php

namespace spec\Kiboko\Component\ETL\Batch\Job;

use Kiboko\Component\ETL\Batch\Job\JobInterface;
use Kiboko\Component\ETL\Batch\Job\JobParameters;
use Kiboko\Component\ETL\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Kiboko\Component\ETL\Batch\Job\JobParameters\DefaultValuesProviderRegistry;
use PhpSpec\ObjectBehavior;

class JobParametersFactorySpec extends ObjectBehavior
{
    const INSTANCE_CLASS = JobParameters::class;

    function let(DefaultValuesProviderRegistry $registry)
    {
        $this->beConstructedWith($registry, self::INSTANCE_CLASS);
    }

    function it_creates_a_job_parameters_with_default_values(
        $registry,
        DefaultValuesProviderInterface $provider,
        JobInterface $job
    ) {
        $job->getName()->willReturn('foo');
        $registry->get($job)->willReturn($provider);
        $provider->getDefaultValues()->willReturn(['my_default_field' => 'my default value']);

        $jobParameters = $this->create($job, ['my_defined_field' => 'my defined value']);

        $jobParameters->shouldReturnAnInstanceOf(JobParameters::class);
        $jobParameters->all()->shouldBe(
            [
                'my_default_field' => 'my default value',
                'my_defined_field' => 'my defined value',
            ]
        );
    }
}
