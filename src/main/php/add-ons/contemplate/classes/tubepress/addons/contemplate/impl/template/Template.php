<?php
/**
 * Copyright 2006 - 2014 TubePress LLC (http://tubepress.com)
 * 
 * This file is part of TubePress (http://tubepress.com)
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * A template used to render strings.
 *
 * @package TubePress\Template
 */
class tubepress_addons_contemplate_impl_template_Template implements tubepress_api_template_TemplateInterface
{
    /**
     * @var array
     */
    private $_context = array();

    /**
     * @var ehough_contemplate_api_Template
     */
    private $_delegate;

    public function __construct(ehough_contemplate_api_Template $delegate)
    {
        $this->_delegate = $delegate;
    }

    /**
     * @return array An associtiave array of template variables. May be empty but never null.
     */
    public function getContext()
    {
        return $this->_context;
    }

    /**
     * Set the variables for this template.
     *
     * @param array $context An associative array of template variables.
     *
     * @throws InvalidArgumentException If a non-associative array is passed in.
     *
     * @return void
     */
    public function setContext(array $context)
    {
        if (!tubepress_impl_util_LangUtils::isAssociativeArray($context)) {

            throw new InvalidArgumentException('tubepress_api_template_TemplateInterface::setContext() requires an associative array.');
        }

        $this->_context = $context;
    }

    /**
     * @return string The rendered template.
     */
    public function toString()
    {
        $this->_delegate->reset();

        foreach ($this->_context as $key => $value) {

            $this->_delegate->setVariable($key, $value);
        }

        return $this->_delegate->toString();
    }
}