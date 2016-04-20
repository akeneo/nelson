<?php

namespace spec\Akeneo\Nelson;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TranslationFileSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            '/tmp/src/AwesomeProject/Bundle/YoloBundle/Resources/translations/validators.en.yml',
            '/tmp/',
            'Project'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Nelson\TranslationFile');
    }

    function it_returns_pattern()
    {
        $this->getPattern()
            ->shouldReturn(
                '/Project/src/AwesomeProject/Bundle/YoloBundle/'.
                'Resources/translations/validators.%locale_with_underscore%.%file_extension%'
            );
    }
}
