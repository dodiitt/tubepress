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
 * @covers tubepress_jwplayer_impl_options_ui_JwPlayerFieldProvider<extended>
 */
class tubepress_test_jwplayer_impl_options_ui_JwPlayerFieldProviderTest extends tubepress_test_TubePressUnitTest
{
    /**
     * @var tubepress_jwplayer_impl_options_ui_JwPlayerFieldProvider
     */
    private $_sut;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockTranslator;

    /**
     * @var ehough_mockery_mockery_MockInterface[]
     */
    private $_mockFields;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockField;

    public function onSetup()
    {
        $this->_mockTranslator = $this->mock(tubepress_core_translation_api_TranslatorInterface::_);
        $this->_mockField = $this->mock('tubepress_core_options_ui_api_FieldInterface');
        $this->_mockFields = array($this->_mockField);

        $this->_sut = new tubepress_jwplayer_impl_options_ui_JwPlayerFieldProvider(

            $this->_mockTranslator,
            $this->_mockFields
        );
    }

    public function testDefaults()
    {
        $map = array(

            'player-category' => array(

                tubepress_jwplayer_api_Constants::OPTION_COLOR_BACK,
                tubepress_jwplayer_api_Constants::OPTION_COLOR_FRONT,
                tubepress_jwplayer_api_Constants::OPTION_COLOR_LIGHT,
                tubepress_jwplayer_api_Constants::OPTION_COLOR_SCREEN)
        );

        $this->_mockTranslator->shouldReceive('_')->once()->with('JW Player')->andReturn('xyz');
        $this->assertEquals(array(), $this->_sut->getCategories());
        $this->assertTrue($this->_sut->fieldsShouldBeInSeparateBoxes());
        $this->assertFalse($this->_sut->isAbleToBeFilteredFromGui());
        $this->assertEquals('xyz', $this->_sut->getTranslatedDisplayName());
        $this->assertEquals($map, $this->_sut->getCategoryIdsToFieldIdsMap());
        $this->assertEquals('jwplayer-field-provider', $this->_sut->getId());
        $this->assertSame($this->_mockFields, $this->_sut->getFields());
    }
}