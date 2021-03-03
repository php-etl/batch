<?php

namespace spec\Kiboko\Component\Workflow\Item;

use Kiboko\Component\Workflow\Item\InvalidItemInterface;
use PhpSpec\ObjectBehavior;

class InvalidItemExceptionSpec extends ObjectBehavior
{
    function let(InvalidItemInterface $invalidItem)
    {
        $invalidItem->getInvalidData()->willReturn(['foo' => 'fighter']);

        $this->beConstructedWith(
            'Tango is down, I repeat...',
            $invalidItem
        );
    }

    function it_provides_the_message()
    {
        $this->getMessage()->shouldReturn('Tango is down, I repeat...');
    }

    function it_provides_the_invalid_item($invalidItem)
    {
        $this->getItem()->shouldReturn($invalidItem);
    }
}
