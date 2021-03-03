<?php

namespace Kiboko\Component\Workflow\Normalizer\Structured;

use Kiboko\Component\Workflow\Model\JobInstance;
use Kiboko\Component\Workflow\Model\JobInstanceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * {@inheritdoc}
     *
     * @param JobInstanceInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $results = [
            'code'          => $object->getCode(),
            'label'         => $object->getLabel(),
            'connector'     => $object->getConnector(),
            'type'          => $object->getType(),
            'configuration' => $this->normalizeConfiguration($object),
        ];

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobInstance && in_array($format, $this->supportedFormats);
    }

    /**
     * Get normalized configuration
     *
     * @param JobInstanceInterface $job
     *
     * @return mixed
     */
    protected function normalizeConfiguration(JobInstanceInterface $job)
    {
        return $job->getRawParameters();
    }
}
