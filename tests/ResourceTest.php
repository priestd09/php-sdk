<?php

namespace Moltin\SDK\Tests;

use Moltin;
use Moltin\Response;
use Mockery;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $storage;
    private $requestLibrary;
    private $underTest;

    public function setUp()
    {
        $this->client = Mockery::mock('Moltin\Client');
        $this->client->shouldReceive('getAPIEndpoint')
            ->andReturn('https://api.moltin.com')
            ->shouldReceive('getAuthEndpoint')
            ->andReturn('https://api.moltin.com/oauth/access_token')
            ->shouldReceive('getClientID')
            ->andReturn('123')
            ->shouldReceive('getClientSecret')
            ->andReturn('456');

        $this->storage = Mockery::mock('Moltin\Session');
        $sessonObject = new \stdClass();
        $sessonObject->access_token = '7893e06821bfbee0ea82afe2942dab734713cf5a';
        $sessonObject->expires = time() + 600;
        $this->storage->shouldReceive('getKey')
            ->with('authentication')
            ->andReturn($sessonObject);

        $response = Mockery::mock('Moltin\Response');

        $this->requestLibrary = Mockery::mock('Moltin\Request');
        $this->requestLibrary->shouldReceive('make')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('setURL')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('addHeaders')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('setBody')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('addHeader')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('setMethod')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('setQueryStringParams')
            ->andReturn($response)
            ->shouldReceive('getRaw')
            ->andReturn(new \StdClass);

        $this->underTest = new Moltin\Resources\Products($this->client, $this->requestLibrary, $this->storage);
    }

    public function testFilter()
    {
        $this->assertEquals($this->underTest->filter(), $this->underTest);
    }

    public function testSort()
    {
        $this->assertEquals($this->underTest->sort(false), $this->underTest);
    }

    public function testLimit()
    {
        $this->assertEquals($this->underTest->limit(10), $this->underTest);
    }

    public function testOffset()
    {
        $this->assertEquals($this->underTest->offset(5), $this->underTest);
    }

    public function testGetRelationshipTypeReturnsValueWithValidType()
    {
        $this->assertEquals($this->underTest->getRelationshipType('categories'), 'category');
    }

    public function testGetRelationshipTypeReturnsFalseWithInvalidType()
    {
        $this->assertEquals($this->underTest->getRelationshipType('doesntexist'), false);
    }

    public function testBuildRelationshipDataReturnsValidArray()
    {
        $expected = [
            ['type' => 'category', 'id' => 'fe743255-b387-4a37-a712-6e341e81a6ab'],
            ['type' => 'category', 'id' => '838ff042-7d4b-4b4b-8d6c-443e7368e73a']
        ];

        $this->assertEquals($this->underTest->buildRelationshipData('category', ['fe743255-b387-4a37-a712-6e341e81a6ab', '838ff042-7d4b-4b4b-8d6c-443e7368e73a']), $expected);
    }

    public function testBuildRelationshipDataWithNullReturnsNull()
    {
        $this->assertEquals($this->underTest->buildRelationshipData('category', null), null);
    }

    public function testBuildRelationshipEmptyArrayReturnsNull()
    {
        $this->assertEquals($this->underTest->buildRelationshipData('category', []), null);
    }

    public function testCanGetAttributes()
    {
        $this->assertInstanceof(Response::class, $this->underTest->attributes());
    }

    public function testCanMakeGetAllRequest()
    {
        $this->assertInstanceof(Response::class, $this->underTest->get());
    }

    public function testCanMakeGetByIDRequest()
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Response::class, $this->underTest->get($id));
    }

    public function testCanMakeDeleteRequest()
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Response::class, $this->underTest->delete($id));
    }

    public function testCanMakeUpdateRequest()
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Response::class, $this->underTest->update($id, []));
    }

    public function testCanMakeCreateRequest()
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Response::class, $this->underTest->create([]));
    }

    public function testCanCreateRelationships()
    {
        $this->assertInstanceof(Response::class, $this->underTest->createRelationships('c9b96b2f-574d-43f7-be53-3737959ddbb1', 'categories', []));
    }

    public function testCanDeleteRelationships()
    {
        $this->assertInstanceof(Response::class, $this->underTest->deleteRelationships('c9b96b2f-574d-43f7-be53-3737959ddbb1', 'categories', []));
    }

    public function testCanUpdateRelationships()
    {
        $this->assertInstanceof(Response::class, $this->underTest->updateRelationships('c9b96b2f-574d-43f7-be53-3737959ddbb1', 'categories', []));
    }

    /**
     * @expectedException Moltin\Exceptions\InvalidRelationshipTypeException
     */
    public function testRelationshipCallWithInvalidTypeThrowsException()
    {
        $this->underTest->updateRelationships('c9b96b2f-574d-43f7-be53-3737959ddbb1', 'notreal', []);
    }

    public function testGetAccessTokenMakesAuthenticationCall()
    {
        $atResponse = new \stdClass;
        $atResponse->access_token = 'ef6206afa0a8a95d342c10b9eadb3082e19c8021';
        $response = Mockery::mock('Moltin\Response');
        $response->shouldReceive('getRaw')
            ->andReturn($atResponse);

        $this->storage = Mockery::mock('Moltin\Session');
        $this->storage->shouldReceive('getKey')
            ->with('authentication')
            ->andReturn(false)
            ->shouldReceive('setKey');

        $requestLibrary = Mockery::mock('Moltin\Request');
        $requestLibrary->shouldReceive('make')
            ->andReturn($requestLibrary)
            ->shouldReceive('setURL')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeaders')
            ->andReturn($requestLibrary)
            ->shouldReceive('setBody')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeader')
            ->andReturn($requestLibrary)
            ->shouldReceive('setMethod')
            ->andReturn($requestLibrary)
            ->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('getRaw')
            ->andReturn(new \StdClass);

        $test = new Moltin\Resources\Products($this->client, $requestLibrary, $this->storage);

        $this->assertEquals('ef6206afa0a8a95d342c10b9eadb3082e19c8021', $test->getAccessToken());
    }

    /**
     * @expectedException Moltin\Exceptions\AuthenticationException
     */
    public function testGetAccessTokenWhichIsForbiddenThrowsException()
    {
        $response = Mockery::mock('Moltin\Response');
        $response->shouldReceive('getRaw')
            ->andReturn(false);

        $this->storage = Mockery::mock('Moltin\Session');
        $this->storage->shouldReceive('getKey')
            ->with('authentication')
            ->andReturn(false)
            ->shouldReceive('setKey');

        $requestLibrary = Mockery::mock('Moltin\Request');
        $requestLibrary->shouldReceive('make')
            ->andReturn($requestLibrary)
            ->shouldReceive('setURL')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeaders')
            ->andReturn($requestLibrary)
            ->shouldReceive('setBody')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeader')
            ->andReturn($requestLibrary)
            ->shouldReceive('setMethod')
            ->andReturn($requestLibrary)
            ->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('getRaw')
            ->andReturn(new \StdClass);

        $test = new Moltin\Resources\Products($this->client, $requestLibrary, $this->storage);
        $test->makeAuthenticationCall();
    }

    public function testBuildQueryStringParams()
    {
        $this->underTest->limit(5)->offset(3)->sort('name');
        $this->assertEquals(['page' => ['limit' => 5, 'offset' => 3], 'sort' => 'name'], $this->underTest->buildQueryStringParams());
    }

}
