<?php

declare(strict_types=1);

namespace Tests\Akeneo\Crowdin;

use Akeneo\Crowdin\Api\LanguageStatus;
use Akeneo\Crowdin\Api\Status;
use Akeneo\Crowdin\Client;
use Akeneo\Crowdin\TranslatedProgressSelector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TranslatedProgressSelectorIntegration extends TestCase
{
    public function testWithDefaultBranch(): void
    {
        $crowdinClient = $this->createMockCLient();

        $progressSelector = new TranslatedProgressSelector($crowdinClient, new EventDispatcher());
        $output = new BufferedOutput();
        $progressSelector->display($output);

        $outputResult = $output->fetch();

        $this->assertStringStartsWith('Languages exported for master branch (0%)', $outputResult);
        $this->assertLineInTable($outputResult, 'locale', 'percentage');

        $this->assertLineInTable($outputResult, 'en-GB', '100%');
        $this->assertLineInTable($outputResult, 'fr', '98%');
    }

    public function testWithOneExplicitBranch(): void
    {
        $crowdinClient = $this->createMockCLient();

        $progressSelector = new TranslatedProgressSelector(
            $crowdinClient,
            new EventDispatcher(),
            0,
            null,
            ['7.0']
        );
        $output = new BufferedOutput();
        $progressSelector->display($output);

        $outputResult = $output->fetch();

        $this->assertStringStartsWith('Languages exported for 7.0 branch (0%)', $outputResult);
        $this->assertLineInTable($outputResult, 'locale', 'percentage');

        $this->assertLineInTable($outputResult, 'en-GB', '43%');
        $this->assertLineInTable($outputResult, 'fr', '100%');
    }

    public function testWithExplicitBranches(): void
    {
        $crowdinClient = $this->createMockCLient();

        $progressSelector = new TranslatedProgressSelector(
            $crowdinClient,
            new EventDispatcher(),
            0,
            null,
            ['7.0', 'master']
        );
        $output = new BufferedOutput();
        $progressSelector->display($output);

        $outputResult = $output->fetch();

        $this->assertStringStartsWith('Languages exported for 7.0 branch (0%)', $outputResult);
        $this->assertStringContainsString('Languages exported for master branch (0%)', $outputResult);

        $this->assertLineInTable($outputResult, 'locale', 'percentage');

        $this->assertLineInTable($outputResult, 'en-GB', '100%');
        $this->assertLineInTable($outputResult, 'fr', '98%');

        $this->assertLineInTable($outputResult, 'en-GB', '43%');
        $this->assertLineInTable($outputResult, 'fr', '100%');
    }

    private function assertLineInTable(string $outputResult, string $value1, string $value2): void
    {
        $pattern = sprintf('/\| %s \s*\| %s \s*\|/', $value1, $value2);
        $this->assertMatchesRegularExpression($pattern, $outputResult);
    }

    private function createMockCLient(): Client
    {
        $status = $this->createMock(Status::class);
        $status->method('execute')->willReturn(
            file_get_contents(__DIR__ . '/../../resources/crowdin/all_crowdin_codes.xml')
        );

        $languageStatus = $this->createMock(LanguageStatus::class);
        $languageStatus->method('setLanguage')->willReturn(null);
        $languageStatus->expects($this->atLeast(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                file_get_contents(__DIR__ . '/../../resources/crowdin/approved_counts_en-GB.xml'),
                file_get_contents(__DIR__ . '/../../resources/crowdin/approved_counts_fr.xml'),
                file_get_contents(__DIR__ . '/../../resources/crowdin/approved_counts_en-GB.xml'),
                file_get_contents(__DIR__ . '/../../resources/crowdin/approved_counts_fr.xml')
            );
        ;

        $crowdinClient = $this->createMock(Client::class);
        $crowdinClient->method('api')
            ->will(
                $this->returnValueMap(
                    [
                        ['status', $status],
                        ['language-status', $languageStatus],
                    ]
                )
            );

        return $crowdinClient;
    }
}
