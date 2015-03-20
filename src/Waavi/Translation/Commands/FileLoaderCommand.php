<?php namespace Waavi\Translation\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Waavi\Translation\Providers\LanguageProvider as LanguageProvider;
use Waavi\Translation\Providers\LanguageEntryProvider as LanguageEntryProvider;

class FileLoaderCommand extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'translator:load';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = "Load language files into the database.";

  /**
   *  Create a new mixed loader instance.
   *
   *  @param  LanguageProvider $languageProvider
   *  @param  LanguageEntryProvider $languageEntryProvider
   *  @param  \Waavi\Translation\Loaders\FileLoader $fileLoader
   */
  public function __construct($languageProvider, $languageEntryProvider, $fileLoader)
  {
    parent::__construct();
    $this->languageProvider       = $languageProvider;
    $this->languageEntryProvider  = $languageEntryProvider;
    $this->fileLoader             = $fileLoader;
    $this->finder                 = new Filesystem();
    $this->path                   = app_path().DIRECTORY_SEPARATOR.'lang';
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
    public function fire()
    {
        $localeDirs = $this->finder->directories($this->path);

        foreach($localeDirs as $localeDir)
        {
            # detect the locale
            $locale = str_replace($this->path.DIRECTORY_SEPARATOR, '', $localeDir);

            # load local model
            $language = $this->languageProvider->findByLocale($locale);

            if ($language)
            {
                $langFiles = $this->finder->files($localeDir);

                foreach($langFiles as $langFile)
                {
                    $group = str_replace(
                        array('/', $localeDir.DIRECTORY_SEPARATOR, '.php'),
                        array(DIRECTORY_SEPARATOR,'',''),
                        $langFile
                    );

                    $lines = $this->fileLoader->loadRawLocale($locale, $group);

                    $this->languageEntryProvider->loadArray(
                        $lines,
                        $language,
                        $group,
                        null,
                        $locale == $this->fileLoader->getDefaultLocale()
                    );
                }
            }
        }
    }

}