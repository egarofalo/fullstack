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

    private function renameThemeDirectory(
        OutputInterface $output,
        string $themeDirectory,
        string $newThemeDirectory
    ) {
        if ($this->filesystem->exists("{$this->themesDir}/{$newThemeDirectory}")) {
            $output->writeln([
                '<error>',
                "The folder {$newThemeDirectory} already exists in the themes directory.",
                'Choose another theme name.',
                '</error>',
            ]);

            return false;
        }

        try {
            $this->filesystem->rename(
                "{$this->themesDir}/{$themeDirectory}",
                "{$this->themesDir}/{$newThemeDirectory}"
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

    private function updateStyleFileContents(array $styleFileContents, string $metadataKey, ?string $metadataValue)
    {
        if (empty($metadataValue)) {
            return $styleFileContents;
        }

        $metadataPattern = "/^(\s*\*?\s*{$metadataKey}\s*:).+$/i";
        $updated = false;

        foreach ($styleFileContents as $key => $line) {
            if (preg_match($metadataPattern, $line)) {
                $styleFileContents[$key] = preg_replace(
                    $metadataPattern,
                    "$1 {$metadataValue}",
                    $line
                );
                $updated = true;

                break;
            }
        }

        if (!$updated) {
            $themeNamePattern = "/^(\s*\*?\s*)Theme +Name(\s*:).+$/i";

            foreach ($styleFileContents as $key => $line) {
                if (preg_match($themeNamePattern, $line)) {
                    array_splice(
                        $styleFileContents,
                        $key + 1,
                        0,
                        preg_replace(
                            $themeNamePattern,
                            "$1{$metadataKey}$2 {$metadataValue}",
                            $line
                        )
                    );

                    break;
                }
            }
        }

        return $styleFileContents;
    }

    private function updateThemeMetadata(
        OutputInterface $output,
        string $themeDirectory,
        $metadataKey,
        $metadataValue
    ) {
        $styleFileContents = @file("{$this->themesDir}/{$themeDirectory}/style.css");

        if ($styleFileContents === false) {
            $output->writeln([
                '<error>',
                "An error ocurred while updating the {$metadataKey}.",
                'The style.css theme file can not be read.',
                '</error>',
            ]);

            return false;
        }

        $styleFileContents = $this->updateStyleFileContents($styleFileContents, $metadataKey, $metadataValue);

        try {
            $this->filesystem->dumpFile(
                "{$this->themesDir}/{$themeDirectory}/style.css",
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

    private function getThemeDirectory(InputInterface $input, OutputInterface $output)
    {
        $slugify = new Slugify();
        $helper = $this->getHelper('question');
        $output->writeln('');
        $themeNameQuestion = new Question('Please enter the Theme Name (or theme directory name) to be change: ');
        $themeName = $helper->ask($input, $output, $themeNameQuestion);

        while (empty($themeName)) {
            $output->writeln([
                '<error>',
                'You must enter a theme name.',
                '</error>',
            ]);
            $themeName = $helper->ask($input, $output, $themeNameQuestion);
        }

        $themeDirectory = $slugify->slugify($themeName);

        while (!$this->filesystem->exists("{$this->themesDir}/{$themeDirectory}")) {
            $output->writeln([
                '<error>',
                "The folder {$themeDirectory} not exists in the themes directory.",
                '</error>',
            ]);

            $themeDirectoryQuestion = new Question('Enter the theme directory name instead of theme name: ');
            $themeDirectory = $helper->ask($input, $output, $themeDirectoryQuestion);

            while (empty($themeDirectory)) {
                $output->writeln([
                    '<error>',
                    'You must enter a theme directory name.',
                    '</error>',
                ]);
                $themeDirectory = $helper->ask($input, $output, $themeDirectoryQuestion);
            }
        }

        return $themeDirectory;
    }

    private function getAnswer(InputInterface $input, OutputInterface $output, string $question)
    {
        $helper = $this->getHelper('question');
        $output->writeln('');
        $questionObj = new Question($question);
        return $helper->ask($input, $output, $questionObj);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $slugify = new Slugify();
        $themeDirectory = $this->getThemeDirectory($input, $output);
        $newThemeDirectory = $themeDirectory;
        $newThemeName = $this->getAnswer($input, $output, 'Please enter the new Theme Name (keep blank to skip): ');

        // update theme directory
        if (!empty($newThemeName)) {
            $newThemeDirectory = $slugify->slugify($newThemeName);

            if (!$this->renameThemeDirectory($output, $themeDirectory, $newThemeDirectory)) {
                return;
            }
        }

        $metadata = [
            [
                'metadata' => 'Theme Name',
                'value' => $newThemeName,
            ],
            [
                'metadata' => 'Theme URI',
                'value' => $this->getAnswer($input, $output, 'Please enter "Theme URI" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Author',
                'value' => $this->getAnswer($input, $output, 'Please enter "Author" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Author URI',
                'value' => $this->getAnswer($input, $output, 'Please enter "Author URI" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Description',
                'value' => $this->getAnswer($input, $output, 'Please enter "Description" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Requires at least',
                'value' => $this->getAnswer($input, $output, 'Please enter "Requires at least" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Tested up to',
                'value' => $this->getAnswer($input, $output, 'Please enter "Tested up to" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Requires PHP',
                'value' => $this->getAnswer($input, $output, 'Please enter "Requires PHP" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Version',
                'value' => $this->getAnswer($input, $output, 'Please enter "Version" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'License',
                'value' => $this->getAnswer($input, $output, 'Please enter "License" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'License URI',
                'value' => $this->getAnswer($input, $output, 'Please enter "License URI" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Text Domain',
                'value' => $this->getAnswer($input, $output, 'Please enter "Text Domain" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Domain Path',
                'value' => $this->getAnswer($input, $output, 'Please enter "Domain Path" metadata (keep blank to skip): '),
            ],
            [
                'metadata' => 'Tags',
                'value' => $this->getAnswer($input, $output, 'Please enter "Tags" metadata (keep blank to skip): '),
            ]
        ];

        foreach ($metadata as $item) {
            if (!$this->updateThemeMetadata(
                $output,
                $newThemeDirectory,
                $item['metadata'],
                $item['value']
            )) {
                $output->writeln([
                    '<error>',
                    "The \"{$item['metadata']}\" metadata was not updated in the style.css file.",
                    'Do it manually after the update process finish.',
                    '</error>',
                ]);
            }
        }
    }
}
