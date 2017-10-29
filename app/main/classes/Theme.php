<?php

namespace Main\Classes;

use App;
use File;
use Igniter\Flame\Pagic\Source\FileSource;
use Main\Template\Page as PageTemplate;

class Theme
{
    /**
     * @var string The theme name
     */
    public $name;

    /**
     * @var string Theme label.
     */
    public $label;

    /**
     * @var string Specifies a description to accompany the theme
     */
    public $description;

    /**
     * @var string The theme version
     */
    public $version;

    /**
     * @var string The theme author
     */
    public $author;

    /**
     * @var string List of extension code and version required by this theme
     */
    public $requires = [];

    /**
     * @var string The theme path absolute base path
     */
    public $path;

    /**
     * @var string The theme path relative to base path
     */
    public $publicPath;

    /**
     * @var boolean Determine if this theme should be loaded (false) or not (true).
     */
    public $disabled;

    /**
     * @var string Other theme code this theme was derived from.
     */
    public $parent;

    /**
     * @var string Path to the screenshot image, relative to this theme folder.
     */
    public $screenshot;

    /**
     * @var array Raw theme configuration.
     */
    public $config;

    /**
     * @var array Raw theme partials configuration.
     */
    public $partials;

    /**
     * @var array Raw theme customizer configuration.
     */
    public $customizers;

    public $active;

    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = realpath($path);
    }

    public function setUpAs($config)
    {
        $this->publicPath = File::localToPublic($this->path);
        $this->config = $this->evalConfig($config);
        $this->registerPagicSource();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDirName()
    {
        return basename($this->path);
    }

    public function requires($require)
    {
        if (!is_array($require))
            $require = [$require];

        return $require;
    }

    public function setCustomizers(array $config = [])
    {
//        if (!\File::isFile())
        if (isset($config['customize']))
            $this->customizers = $config['customize'];

        if (isset($config['partial_areas']))
            $this->partials = $config['partial_areas'];
    }

    /**
     * Determines if a theme is a child by looking in the theme meta file.
     *
     * @param $themeCode
     *
     * @return bool
     */
    public function isChild()
    {
        return strlen($this->parent) > 1;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function listPages()
    {
        return PageTemplate::listInTheme($this);
    }

    /**
     * Process options and apply them to this object.
     *
     * @param array $config
     *
     * @return array
     */
    protected function evalConfig($config)
    {
        if (isset($config['code']))
            $this->name = $config['code'];

        if (isset($config['name']))
            $this->label = $config['name'];

        if (isset($config['domain']))
            $this->domain = $config['domain'];

        if (isset($config['description']))
            $this->description = $config['description'];

        if (isset($config['version']))
            $this->version = $config['version'];

        if (isset($config['author']))
            $this->author = $config['author'];

        if (isset($config['screenshot']))
            $this->screenshot = $config['screenshot'];

        if (!$this->screenshot)
            $this->screenshot = $this->publicPath.'/screenshot.png';

        if (isset($config['require']))
            $this->requires = $this->requires($config['require']);

        if (isset($config['parent']))
            $this->parent = $config['parent'];

        if (array_key_exists('disabled', $config))
            $this->disabled = $config['disabled'];

        return $config;
    }

    /**
     * Ensures this theme is registered as a Pagic source.
     * @return void
     */
    protected function registerPagicSource()
    {
        $resolver = App::make('pagic');

        if (!$resolver->hasSource($this->getDirName())) {
            $source = new FileSource($this->getPath(), App::make('files'));
            $resolver->addSource($this->getDirName(), $source);
        }
    }
}