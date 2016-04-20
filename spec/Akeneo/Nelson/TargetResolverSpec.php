<?php

namespace spec\Akeneo\Nelson;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TargetResolverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            '/Resources/translations'   => '',
            'src/AwesomeProject/Bundle' => 'Project',
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Nelson\TargetResolver');
    }

    function it_apply_rules()
    {
        $this
            ->getTarget('/tmp', '/tmp/src/AwesomeProject/Bundle/YoloBundle/Resources/translations/validators.en.yml')
            ->shouldReturn('Project/YoloBundle/validators.en.yml');
    }

    function it_returns_directory()
    {
        $this
            ->getTargetDirectory('/tmp', '/tmp/src/AwesomeProject/Bundle/YoloBundle/Resources/translations/validators.en.yml')
            ->shouldReturn('Project/YoloBundle');
    }
}
