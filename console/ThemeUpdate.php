<?php

namespace Codevelopers\Fullstack\Console;

use Cocur\Slugify\Slugify;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\IOException;

class ThemeUpdate extends Command
{
    protected static $defaultName = 'theme:update';
    private $themesDir;
    private $filesystem;

    public function __construct()
    {
        parent::__construct();
        $this->themesDir = __DIR__ . '/../public/content/themes';
        $this->filesystem = new Filesystem();
    }

    protected function configure()
    {
        $this->setDescription('Change the theme name and optionally another information of it')
            ->setHelp('Change the name of the theme and optionally another information such as Version, Description, etc.');
    }

    private function updateFolderName(
        OutputInterface $output,
        string $oldFolderName,
        string $newFolderName
    ) {
        if ($this->filesystem->exists("{$this->themesDir}/{$newFolderName}")) {
            $output->writeln([
                '<error>',
                "The folder {$newFolderName} already exists in the themes directory.",
                'Choose another theme name.',
                '</error>',
            ]);

            return false;
        }

        try {
            $this->filesystem->rename(
                "{$this->themesDir}/{$oldFolderName}",
                "{$this->themesDir}/{$newFolderName}"
            );
        } catch (IOException $e) {
            $output->writeln([
                '<error>',
                'An error ocurred while updating the theme folder name.',
                $e->getMessage(),
                '</error>',
            ]);

            return false;
        }

        return true;
    }

    private function updateThemeMetadata(
        OutputInterface $output,
        string $folderName,
        $metadataKey,
        $metadataValue
    ) {
        $styleFileContents = @file("{$this->themesDir}/{$folderName}/style.css");

        if ($styleFileContents === false) {
            $output->writeln([
                '<error>',
                "An error ocurred while updating the {$metadataKey}.",
                'The style.css theme file can not be read.',
                '</error>',
            ]);

            return false;
        }

        $pattern = "/^(\s*\*?\s*{$metadataKey}\s*:).+$/i";

        foreach ($styleFileContents as $key => $line) {
            if (preg_match($pattern, $line)) {
                $styleFileContents[$key] = preg_replace(
                    $pattern,
                    "$1 {$metadataValue}",
                    $line
                );

                break;
            }
        }

        try {
            $this->filesystem->dumpFile(
                "{$this->themesDir}/{$folderName}/style.css",
                $styleFileContents
            );
        } catch (IOException $e) {
            $output->writeln([
                '<error>',
                "An error ocurred while updating the {$metadataKey}.",
                $e->getMessage(),
                '</error>',
            ]);

            return false;
        }

        return true;
    }

    private function getThemeNameResponse(InputInterface $input, OutputInterface $output)
    {
        $slugify = new Slugify();
        $helper = $this->getHelper('question');
        $output->writeln('');
        $question = new Question('Please enter the theme name (or theme folder name) to be change: ');
        $themeName = $helper->ask($input, $output, $question);

        while (empty($themeName)) {
            $output->writeln([
                '<error>',
                'You must enter a theme name.',
                '</error>',
            ]);
            $themeName = $helper->ask($input, $output, $question);
        }

        $folderName = $slugify->slugify($themeName);

        if (!$this->filesystem->exists("{$this->themesDir}/{$folderName}")) {
            $output->writeln([
                '<error>',
                "The folder {$folderName} not exists in the themes directory.",
                'Enter another theme name.',
                '</error>',
            ]);
            $themeName = false;
        }

        return $themeName;
    }

    private function getNewThemeNameResponse(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the new theme name: ');
        $newThemeName = $helper->ask($input, $output, $question);

        while (empty($newThemeName)) {
            $output->writeln([
                '<error>',
                'You must enter a theme name.',
                '</error>',
            ]);
            $newThemeName = $helper->ask($input, $output, $question);
        }

        return $newThemeName;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $slugify = new Slugify();
        $themeName = $this->getThemeNameResponse($input, $output);

        if (empty($themeName)) {
            return;
        }

        $folderName = $slugify->slugify($themeName);
        $newThemeName = $this->getNewThemeNameResponse($input, $output);
        $newFolderName = $slugify->slugify($newThemeName);

        if (!$this->updateFolderName($output, $folderName, $newFolderName)) {
            return;
        }

        if (!$this->updateThemeMetadata(
            $output,
            $newFolderName,
            'Theme Name',
            $newThemeName
        )) {
            $output->writeln([
                '<error>',
                'The Theme Name was not updated in the style.css file.',
                'Do it manually after the update process finish.',
                '</error>',
            ]);
        }

        $output->writeln([
            '<info>',
            'Theme updated successfully.',
            '</info>',
        ]);
    }
}
