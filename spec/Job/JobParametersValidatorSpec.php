<?php

namespace spec\Kiboko\Component\ETL\Batch\Job;

use Kiboko\Component\ETL\Batch\Job\JobInterface;
use Kiboko\Component\ETL\Batch\Job\JobParameters;
use Kiboko\Component\ETL\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Kiboko\Component\ETL\Batch\Job\JobParameters\ConstraintCollectionProviderRegistry;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JobParametersValidatorSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, ConstraintCollectionProviderRegistry $registry)
    {
        $this->beConstructedWith($validator, $registry);
    }

    function it_validates_a_job_parameters(
        $validator,
        $registry,
        ConstraintCollectionProviderInterface $provider,
        JobInterface $job,
        JobParameters $jobParameters
    ) {
        $registry->get($job)->willReturn($provider);
        $provider->getConstraintCollection()->willReturn(['fields' => 'my constraints']);
        $jobParameters->all()->willReturn(['my params']);
        $validator
            ->validate(['my params'], ['fields' => 'my constraints'], ['MyValidationGroup', 'Default'])
            ->shouldBeCalled();

        $this->validate($job, $jobParameters, ['MyValidationGroup', 'Default']);
    }
}
