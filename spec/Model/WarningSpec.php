<?php

namespace spec\Kiboko\Component\Workflow\Model;

use Kiboko\Component\Workflow\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class WarningSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->beConstructedWith(
            $stepExecution,
            'my reason',
            ['myparam' => 'mavalue'],
            ['myitem' => 'myvalue']
        );
    }

    function it_provides_a_step_execution($stepExecution)
    {
        $this->getStepExecution()->shouldReturn($stepExecution);
    }

    function it_provides_array_format()
    {
        $this->toArray()->shouldReturn(
            [
                'reason' => 'my reason',
                'reasonParameters' => [
                    'myparam' => 'mavalue'
                ],
                'item' => [
                    'myitem' => 'myvalue'
                ]
            ]
        );
    }
}
