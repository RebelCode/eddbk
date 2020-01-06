<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Event\EventFactoryInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * Class TemplateManager.
 *
 * @since [*next-version*]
 *
 * Template manager is responsible for registering templates in given
 * event manager from container.
 *
 * It register all defined templates as actions in event manager. All templates
 * are prefixed with `eddbk` or any other given prefix. We need prefix when we
 * tweaking from outside.
 *
 * In this module we can work without referencing to the
 * prefix by using `render($templateActionName)` method.
 *
 * Inside render method we will apply prefix and trigger action in event manager after this.
 */
class TemplateManager
{
    /*
     * Provides all required functionality for working with events.
     *
     * @since [*next-version*]
     */
    use EventsConsumerTrait;

    /**
     * Template prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * TemplateManager constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface $eventManager The event manager.
     * @param EventFactoryInterface $eventFactory The event factory.
     * @param string                $prefix       Template's prefix.
     */
    public function __construct($eventManager, $eventFactory, $prefix = 'eddbk')
    {
        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
        $this->prefix = $prefix;
    }

    /**
     * Register all templates in event manager.
     *
     * @since [*next-version*]
     *
     * @param string[] $templates Array of templates paths to be registered.
     */
    public function registerTemplates($templates)
    {
        $this->_register($templates);
    }

    /**
     * Render template by given template action.
     *
     * @since [*next-version*]
     *
     * @param string $template Template path to render.
     *
     * @return string Rendered template.
     */
    public function render($template)
    {
        return $this->_trigger($this->_makeTemplateActionName($template), ['rendered' => ''])->getParam('rendered');
    }

    /**
     * Register given templates in event manager.
     *
     * @since [*next-version*]
     *
     * @param string[] $templates List of paths to templates to register.
     */
    protected function _register($templates)
    {
        foreach ($templates as $template) {
            $templateFilePath = WP_BOOKINGS_UI_MODULE_DIR . '/templates/' . $template . '.phtml';
            $actionName       = $this->_makeTemplateActionName($template);

            $this->_attach($actionName, function ($event) use ($templateFilePath) {
                $event->setParams(['rendered' => $this->_renderFile($templateFilePath)]);
            });
        }
    }

    /**
     * Render file.
     *
     * @since [*next-version*]
     *
     * @param string $realFilePath Path to real template.
     *
     * @return string Rendered file content.
     */
    protected function _renderFile($realFilePath)
    {
        ob_start();
        include $realFilePath;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Create action in event manager according template name.
     *
     * @since [*next-version*]
     *
     * @param string $templatePath Relative path to template.
     *
     * @return string Event name for rendering template.
     */
    protected function _makeTemplateActionName($templatePath)
    {
        return $this->prefix . '_' . $templatePath;
    }
}
