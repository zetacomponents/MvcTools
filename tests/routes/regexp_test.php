<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package MvcTools
 * @subpackage Tests
 */

/**
 * Test the handler classes.
 *
 * @package MvcTools
 * @subpackage Tests
 */
class ezcMvcToolsRegexpRouteTest extends ezcTestCase
{
    public function testMatchEmpty()
    {
        $request = new ezcMvcRequest;
        $request->uri = '';
        $route = new ezcMvcRegexpRoute( '@^$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array(), $request->variables );
    }

    public function testNoMatchEmpty()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'notEmpty';
        $route = new ezcMvcRegexpRoute( '@^$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( null, $routeInfo );
    }

    public function testMatchEmptyDefaultVars()
    {
        $request = new ezcMvcRequest;
        $request->uri = '';
        $route = new ezcMvcRegexpRoute( '@^$@', 'testController', 'action', array( 'default1' => 'Reality is merely an illusion, albeit a very persistent one.' ) );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( 'action', $routeInfo->action );
        self::assertSame( array( 'default1' => 'Reality is merely an illusion, albeit a very persistent one.' ), $request->variables );
    }

    public function testsMatchNonEmptyNoVars()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'people/einstein';
        $route = new ezcMvcRegexpRoute( '@^people/(.*)$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array(), $request->variables );
    }

    public function testsMatchNonEmptyDefaultVar()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'people/einstein';
        $route = new ezcMvcRegexpRoute( '@^people/(.*)$@', 'testController', 'action', array( 'name' => 'rethans' ) );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array( 'name' => 'rethans' ), $request->variables );
    }

    public function testsMatchNonEmptyOneVar()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'people/einstein';
        $route = new ezcMvcRegexpRoute( '@^people/(?P<name>.*)$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array( 'name' => 'einstein' ), $request->variables );
    }

    public function testsMatchNonEmptyDefaultVarReused()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'people/einstein';
        $route = new ezcMvcRegexpRoute( '@^people/(?P<name>.*)$@', 'testController', array( 'name' => 'rethans' ) );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array( 'name' => 'einstein' ), $request->variables );
    }

    public function testsMatchNonEmptyTwoVars()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'people/hawking';
        $route = new ezcMvcRegexpRoute( '@^(?P<group>people)/(?P<name>.*)$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array( 'group' => 'people', 'name' => 'hawking' ), $request->variables );
    }

    public function testsNoMatchNonEmptyTwoVars()
    {
        $request = new ezcMvcRequest;
        $request->uri = 'folks/hawking';
        $route = new ezcMvcRegexpRoute( '@^(?P<group>people)/(?P<name>.*)$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( null, $routeInfo );
    }

    public function testsMatchComplex()
    {
        $route = new ezcMvcRegexpRoute( '@^people(/((?P<nr>[0-9]+)|(?P<name>.+)))?$@', 'testController', 'action', array( 'nr' => '', 'name' => '' ) );

		$request = new ezcMvcRequest;
        $request->uri = 'people/hawking';
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertEquals( array( 'nr' => '', 'name' => 'hawking' ), $request->variables );

		$request = new ezcMvcRequest;
        $request->uri = 'people/42';
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertEquals( array( 'nr' => '42', 'name' => '' ), $request->variables );

		$request = new ezcMvcRequest;
        $request->uri = 'people';
        $routeInfo = $route->matches( $request );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertEquals( array( 'nr' => '', 'name' => '' ), $request->variables );

		$request = new ezcMvcRequest;
        $request->uri = 'people/';
        $routeInfo = $route->matches( $request );
        self::assertEquals( null, $routeInfo );
    }

    public function testPrefixSameDelims()
    {
        $route = new testRegexpRoute( '@^entry/(?P<id>[0-9]+)@', 'ignored' );
        $route->prefix( '@^blog/@' );
        self::assertEquals( '@^blog/entry/(?P<id>[0-9]+)@', $route->getPattern() );
    }

    public function testPrefixDifferentDelims()
    {
        $route = new testRegexpRoute( '@^entry/(?P<id>[0-9]+)@', 'ignored' );
        $route->prefix( '#^blog/#' );
        self::assertEquals( '@^blog/entry/(?P<id>[0-9]+)@', $route->getPattern() );
    }

    public function testPrefixSameDelimsSameSpecifiers()
    {
        $route = new testRegexpRoute( '@^entry/(?P<id>[0-9]+)@i', 'ignored' );
        $route->prefix( '@^blog/@i' );
        self::assertEquals( '@^blog/entry/(?P<id>[0-9]+)@i', $route->getPattern() );
    }

    public function testPrefixSameDelimsDifferentSpecifiers()
    {
        $route = new testRegexpRoute( '@^entry/(?P<id>[0-9]+)@i', 'ignored' );
        try
        {
            $route->prefix( '@^blog/@' );
            self::fail( "Expected exception not thrown." );
        }
        catch ( ezcMvcRegexpRouteException $e )
        {
            self::assertEquals( "The pattern modifiers of the prefix '@^blog/@' and pattern '@^entry/(?P<id>[0-9]+)@i' do not match.", $e->getMessage() );
        }
    }

    public function testPrefixDifferentDelimsSameSpecifiers()
    {
        $route = new testRegexpRoute( '@^entry/(?P<id>[0-9]+)@i', 'ignored' );
        $route->prefix( '#^blog/#i' );
        self::assertEquals( '@^blog/entry/(?P<id>[0-9]+)@i', $route->getPattern() );
    }

    public function testPrefixDifferentDelimsDifferentSpecifiers()
    {
        $route = new testRegexpRoute( '@^entry/(?P<id>[0-9]+)@i', 'ignored' );
        try
        {
            $route->prefix( '#^blog/#' );
            self::fail( "Expected exception not thrown." );
        }
        catch ( ezcMvcRegexpRouteException $e )
        {
            self::assertEquals( "The pattern modifiers of the prefix '#^blog/#' and pattern '@^entry/(?P<id>[0-9]+)@i' do not match.", $e->getMessage() );
        }
    }

    public function testMatchWithDifferentUriMatch()
    {
        $request = new ezcMvcRequest;
        $request->host = 'test.host';
        $request->uri = '/people/hawking';
        $request->requestId = $request->host . $request->uri;

        $route = new testRegexpRouteForFullUri( '@^(?P<site>[a-z]+).host/(?P<group>[a-z]+)/(?P<name>[a-z]+)$@', 'testController' );
        $routeInfo = $route->matches( $request );
        self::assertSame( '@^(?P<site>[a-z]+).host/(?P<group>[a-z]+)/(?P<name>[a-z]+)$@', $routeInfo->matchedRoute );
        self::assertSame( 'testController', $routeInfo->controllerClass );
        self::assertSame( array( 'site' => 'test', 'group' => 'people', 'name' => 'hawking' ), $request->variables );
    }

    static public function generateUrlProvder()
    {
        return array( 
            array( // bare minimum
                '@(?P<arg1>.*)@',
                'val1'
            ),
            array( // with $ suffix
                '@/bar/(?P<arg1>[^/]*)/test/$@',
                '/bar/val1/test/'
            ),
            array( // with ^ prefix
                '@^/bar/(?P<arg1>[^/]*)/test/@',
                '/bar/val1/test/'
            ),
            array( // with ^ prefix and $ suffix
                '@^/bar/(?P<arg1>[^/]*)/test/$@',
                '/bar/val1/test/'
            ),
            array( // with prefix and suffix
                '@/bar/(?P<arg1>[^/]*)/test/@',
                '/bar/val1/test/'
            ),
            array( // with nested parentesis
                '@/bar/(?P<arg1>(foo|bar)[^/]*)/test/@',
                '/bar/val1/test/'
            ),
            array( // with 2 arguments
                '@/bar/(?P<arg1>[^/]*)/(?P<arg2>[^/]*)/@',
                '/bar/val1/val2/',
            ),
        );
    }

    /**
     * @dataProvider generateUrlProvder
     */
    public function testGenerateUrl( $pattern, $expected )
    {
        $fixture = new ezcMvcRegexpRoute( $pattern, 'testController' );

        $result = $fixture->generateUrl( array(
            'arg1' => 'val1',
            'arg2' => 'val2',
        ) );
        
        $this->assertEquals( $expected, $result );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcMvcToolsRegexpRouteTest" );
    }
}
?>
