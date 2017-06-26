<?php

namespace RebelCode\EddBookings\Core\I18n;

use Dhii\I18n\FormatTranslatorInterface;
use Dhii\I18n\StringTranslatingTrait;

class WpTranslator implements FormatTranslatorInterface
{
    /*
     * Adds translation capabilities.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The translation text domain.
     *
     * @since[*next-version*]
     *
     * @var string
     */
    protected $domain;

    /**
     * Constructor.
     *
     * @param string $domain The text domain to use for translations.
     *
     * @since [*next-version*]
     */
    public function __construct($domain)
    {
        $this->_setDomain($domain);
    }

    /**
     * Retrieves the translation text domain.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the translation text domain.
     *
     * @since [*next-version*]
     *
     * @param string $domain The text domain to use for translations.
     *
     * @return $this
     */
    protected function _setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function translate($format, $params = null, $context = null)
    {
        $params = is_array($params)
            ? $params
            : array();

        return $this->__($format, $params, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _translate($string, $context = null)
    {
        return \translate_with_gettext_context($string, $context, $this->_getDomain());
    }
}
