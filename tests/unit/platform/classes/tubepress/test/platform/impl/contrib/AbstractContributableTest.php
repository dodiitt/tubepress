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

abstract class tubepress_test_platform_impl_contrib_AbstractContributableTest extends tubepress_test_TubePressUnitTest
{
    public function testScreenshots()
    {
        $urlFactory = new tubepress_platform_impl_url_puzzle_UrlFactory();
        $first      = $urlFactory->fromString('http://img.com/1.png');
        $second     = $urlFactory->fromString('http://bla.com/2.jpg');
        $third      = $urlFactory->fromString('http://foo.com/2.png');

        $contrib = $this->buildContributable();

        $expected = array(
            array($first, $first),
            array($second, $third)
        );

        $contrib->setScreenshots($expected);

        $actual = $contrib->getScreenshots();
        $this->assertSame($expected, $actual);
    }

    public function testDemoUrl()
    {
        $this->_testUrl('DemoUrl');
    }

    public function testDocsUrl()
    {
        $this->_testUrl('DocumentationUrl');
    }

    public function testBugsUrl()
    {
        $this->_testUrl('BugTrackerUrl');
    }

    public function testDownloadUrl()
    {
        $this->_testUrl('DownloadUrl');
    }

    public function testHomepageUrl()
    {
        $this->_testUrl('HomepageUrl');
    }

    private function _testUrl($method)
    {
        $urlFactory = new tubepress_platform_impl_url_puzzle_UrlFactory();

        $url = $urlFactory->fromString('http://foo.com/bar.txt');

        $setter = "set$method";
        $getter = "get$method";

        $contrib = $this->buildContributable();

        $contrib->$setter($url);

        $actual = $contrib->$getter();
        $this->assertSame($url, $actual);
    }

    public function testKeywords()
    {
        $sut = $this->buildContributable();

        $sut->setKeywords(array('foo', 'bar'));

        $this->assertEquals(array('foo', 'bar'), $sut->getKeywords());
    }

    public function testDescription()
    {
        $sut = $this->buildContributable();

        $sut->setDescription('foobar');

        $this->assertEquals('foobar', $sut->getDescription());
    }

    /**
     * @return tubepress_platform_impl_contrib_AbstractContributable
     */
    protected abstract function buildSut($name, $version, $title, array $authors, array $licenses);

    protected function buildContributable()
    {
        $urlFactory = new tubepress_platform_impl_url_puzzle_UrlFactory();

        $authors = array(
            array(
                'name' => 'author name',
                'url'  => $urlFactory->fromString('http://author.com/foo')
            ),
            array(
                'name'  => 'other author name',
                'email' => 'fake@email.com'
            ),
        );

        $licenses = array(
            array(
                'url'  => $urlFactory->fromString('http://license.com/text.html'),
                'type' => 'some license type'
            )
        );

        return $this->buildSut(
            'some-name',
            tubepress_platform_api_version_Version::parse('1.2.3'),
            'some title',
            $authors,
            $licenses
        );
    }

    public function testBasics()
    {
        $contrib = $this->buildContributable();

        $this->assertEquals('some-name', $contrib->getName());
        $this->assertEquals('1.2.3', $contrib->getVersion()->__toString());
        $this->assertEquals('some title', $contrib->getTitle());

        $authors = $contrib->getAuthors();
        $this->assertCount(2, $authors);
        $author1 = $authors[0];
        $this->assertTrue(is_array($author1));
        $this->assertCount(2, $author1);
        $this->assertEquals('author name', $author1['name']);
        $author1Url = $author1['url'];
        $this->assertInstanceOf('tubepress_platform_api_url_UrlInterface', $author1Url);
        $this->assertEquals('http://author.com/foo', "$author1Url");
        $author2 = $authors[1];
        $this->assertTrue(is_array($author2));
        $this->assertCount(2, $author2, 'Second author has the wrong size');
        $this->assertEquals('other author name', $author2['name']);
        $this->assertEquals('fake@email.com', $author2['email']);

        $licences = $contrib->getLicenses();
        $this->assertCount(1, $licences);
        $license = $licences[0];
        $this->assertTrue(is_array($license));
        $this->assertCount(2, $license);
        $licenseUrl = $license['url'];
        $this->assertInstanceOf('tubepress_platform_api_url_UrlInterface', $licenseUrl);
        $this->assertEquals('http://license.com/text.html', "$licenseUrl");
        $this->assertEquals('some license type', $license['type']);

        $this->assertNull($contrib->getBugTrackerUrl());
        $this->assertNull($contrib->getHomepageUrl());
        $this->assertNull($contrib->getDemoUrl());
        $this->assertNull($contrib->getDownloadUrl());
        $this->assertNull($contrib->getDocumentationUrl());
        $this->assertEquals(array(), $contrib->getKeywords());
        $this->assertEquals(array(), $contrib->getScreenshots());
    }
}