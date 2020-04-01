<?php declare(strict_types=1);

namespace Divi;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

class DownloadCommand extends Command
{
    use LockableTrait;

    public const DEFAULT_URL = 'https://diviexchange.z6.web.core.windows.net/report.html';

    protected static $defaultName = 'download:report';

    protected function configure()
    {
        $this->setDescription('Downloads DIVI report about ICU beds while COVID-19 pandemic.');
        $this->setDefinition(
            new InputDefinition([
                new InputOption(
                    'url',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Absolute URL to download',
                    self::DEFAULT_URL
                ),
            ])
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
            if (!$this->lock()) {
                $errOutput->writeln('The command is already running in another process.');
                return 1;
            }
            $client = HttpClient::create();
            $response = $client->request('GET', $input->getOption('url'));
            $content = $response->getContent();
            $filesystem = new Filesystem;
            $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin'));
            $filename = \dirname(__DIR__) . '/files/' . $now->format('Ymd-Hi') . '-divi-report.html';
            $filesystem->dumpFile($filename, $content);
            return 0;
        } catch (Exception $e) {
            $errOutput->writeln($e->getMessage());
            \error_log((string)$e);
            return 1;
        }
    }
}