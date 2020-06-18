<?php

namespace spec\Kiboko\Component\ETL\Batch\Job\JobParameters;

use Kiboko\Component\ETL\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PhpSpec\ObjectBehavior;

class EmptyConstraintCollectionProviderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['job_name']);
    }

    function it_is_a_contraint_collection_provider()
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    function it_provides_default_constraint_collection()
    {
        $collectionClass = 'Symfony\Component\Validator\Constraints\Collection';
        $this->getConstraintCollection()->shouldReturnAnInstanceOf($collectionClass);
    }
}
