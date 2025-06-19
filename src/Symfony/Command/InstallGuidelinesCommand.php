<?php

namespace Dcblogdev\Junie\Symfony\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;

class InstallGuidelinesCommand extends Command
{
    protected static $defaultName = 'junie:install';

    protected function configure(): void
    {
        $this
            ->setDescription('Install selected guideline documents')
            ->setHelp('This command allows you to install guideline markdown files');

        $this->addOption('all', null, InputOption::VALUE_NONE, 'Install all guidelines');

        $configDocuments = $this->getConfig('documents');
        foreach ($configDocuments as $key => $document) {
            if (($document['enabled'] ?? false) === true) {
                $this->addOption($key, null, InputOption::VALUE_NONE, 'Install '.$document['name']);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $installAll = $input->getOption('all');
        $documents = [];
        $configDocuments = $this->getConfig('documents');

        foreach ($configDocuments as $key => $document) {
            if (($document['enabled'] ?? false) === true) {
                $documents[$key] = $installAll || $input->getOption($key);
            } else {
                $documents[$key] = false;
            }
        }

        if (! $installAll && ! in_array(true, $documents, true)) {
            $documents = $this->promptForDocuments($input, $output, $configDocuments);
        }

        $this->installDocuments($documents, $output);

        $output->writeln('<info>Guidelines installed successfully!</info>');

        return Command::SUCCESS;
    }

    private function promptForDocuments(InputInterface $input, OutputInterface $output, array $configDocuments): array
    {
        $helper = $this->getHelper('question');
        $choices = ['all' => 'All guidelines'];
        foreach ($configDocuments as $key => $document) {
            if (($document['enabled'] ?? false) === true) {
                $choices[$key] = $document['name'];
            }
        }

        $question = new ChoiceQuestion('Which guidelines would you like to install?', $choices);
        $question->setMultiselect(true);
        $selected = $helper->ask($input, $output, $question);
        $installAll = in_array('all', (array) $selected, true);

        $documents = [];
        foreach ($configDocuments as $key => $document) {
            if (($document['enabled'] ?? false) === true) {
                $documents[$key] = $installAll || in_array($key, (array) $selected, true);
            } else {
                $documents[$key] = false;
            }
        }

        return $documents;
    }

    private function installDocuments(array $documents, OutputInterface $output): void
    {
        $filesystem = new Filesystem;
        $outputPath = $this->getConfig('output_path') ?? '.junie';
        $configDocuments = $this->getConfig('documents');

        if (! $filesystem->exists($outputPath)) {
            $filesystem->mkdir($outputPath, 0755);
        }

        foreach ($documents as $document => $install) {
            if ($install && isset($configDocuments[$document]) && ($configDocuments[$document]['enabled'] ?? false)) {
                $documentPath = $configDocuments[$document]['path'] ?? $document.'.md';
                $source = __DIR__.'/../../docs/'.$documentPath;
                $destination = getcwd().'/'.$outputPath.'/'.$documentPath;
                $filesystem->copy($source, $destination, true);
                $output->writeln('Installed <info>'.$configDocuments[$document]['name'].'</info>');
            }
        }

        $this->createIndexFile($outputPath, $documents, $configDocuments);
    }

    private function createIndexFile(string $outputPath, array $documents, array $configDocuments): void
    {
        $filesystem = new Filesystem;
        $content = "# Symfony Guidelines\n\n";
        $content .= "## Available Guidelines\n\n";

        foreach ($documents as $document => $installed) {
            if ($installed && isset($configDocuments[$document]) && ($configDocuments[$document]['enabled'] ?? false)) {
                $title = $configDocuments[$document]['name'];
                $path = $configDocuments[$document]['path'] ?? $document.'.md';
                $content .= "- [$title]($path)\n";
            }
        }

        $filesystem->dumpFile($outputPath.'/index.md', $content);
    }

    private function getConfig(string $key)
    {
        $defaults = require __DIR__.'/../../../config/junie.php';

        return $defaults[$key] ?? null;
    }
}
