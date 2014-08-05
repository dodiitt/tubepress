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
 * @covers tubepress_jwplayer5_impl_listeners_template_JwPlayerTemplateVars
 */
class tubepress_test_jwplayer_impl_embedded_JwPlayerTemplateVarsTest extends tubepress_test_TubePressUnitTest
{
    /**
     * @var tubepress_jwplayer5_impl_listeners_template_JwPlayerTemplateVars
     */
    private $_sut;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockExecutionContext;

    public function onSetup() {


        $this->_mockExecutionContext = $this->mock(tubepress_app_api_options_ContextInterface::_);
        $this->_sut = new tubepress_jwplayer5_impl_listeners_template_JwPlayerTemplateVars($this->_mockExecutionContext);
    }

    public function testLongtail()
    {
        $mockTemplate = $this->mock('tubepress_lib_api_template_TemplateInterface');

        $mockEmbeddedProvider = $this->mock('tubepress_jwplayer5_impl_listeners_embedded_EmbeddedListener');

        $event = $this->mock('tubepress_lib_api_event_EventInterface');
        $event->shouldReceive('getSubject')->once()->andReturn($mockTemplate);
        $event->shouldReceive('getArgument')->once()->with('embeddedProvider')->andReturn($mockEmbeddedProvider);

        $toSet = array(

            tubepress_jwplayer5_api_OptionNames::COLOR_FRONT =>
            tubepress_jwplayer5_api_OptionNames::COLOR_FRONT,

            tubepress_jwplayer5_api_OptionNames::COLOR_LIGHT =>
            tubepress_jwplayer5_api_OptionNames::COLOR_LIGHT,

            tubepress_jwplayer5_api_OptionNames::COLOR_SCREEN =>
            tubepress_jwplayer5_api_OptionNames::COLOR_SCREEN,

            tubepress_jwplayer5_api_OptionNames::COLOR_BACK =>
            tubepress_jwplayer5_api_OptionNames::COLOR_BACK,
        );

        foreach ($toSet as $variableName => $optionName) {

            $this->_mockExecutionContext->shouldReceive('get')->once()->with($optionName)->andReturnUsing(function ($arg) {

                return "<<$arg>>";
            });

            $mockTemplate->shouldReceive('setVariable')->once()->with($variableName, "<<$optionName>>");
        }

        $this->_sut->onEmbeddedTemplate($event);

        $this->assertTrue(true);
    }

    public function testNonLongtail()
    {
        $event = $this->mock('tubepress_lib_api_event_EventInterface');
        $event->shouldReceive('getArgument')->once()->with('embeddedProvider')->andReturn(new stdClass());

        $this->_sut->onEmbeddedTemplate($event);

        $this->assertTrue(true);
    }
}