<?php

namespace spec\Kiboko\Component\ETL\Batch\Normalizer\Structured;

use Kiboko\Component\ETL\Batch\Model\JobInstance;
use Kiboko\Component\ETL\Batch\Model\JobInstanceInterface;
use Kiboko\Component\ETL\Batch\Normalizer\Structured\JobInstanceNormalizer;
use PhpSpec\ObjectBehavior;

class JobInstanceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JobInstanceNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_job_instance_normalization_into_json_and_xml(JobInstanceInterface $jobinstance)
    {
        $this->supportsNormalization($jobinstance, 'csv')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'json')->shouldBe(true);
        $this->supportsNormalization($jobinstance, 'xml')->shouldBe(true);
    }

    function it_normalizes_job_instance(JobInstanceInterface $jobinstance)
    {
        $jobinstance->getCode()->willReturn('product_export');
        $jobinstance->getLabel()->willReturn('Product export');
        $jobinstance->getConnector()->willReturn('myconnector');
        $jobinstance->getType()->willReturn('EXPORT');
        $jobinstance->getRawParameters()->willReturn(
            [
                'delimiter' => ';'
            ]
        );

        $this->normalize($jobinstance)->shouldReturn(
            [
                'code'          => 'product_export',
                'label'         => 'Product export',
                'connector'     => 'myconnector',
                'type'          => 'EXPORT',
                'configuration' => ['delimiter' => ';']
            ]
        );
    }
}
