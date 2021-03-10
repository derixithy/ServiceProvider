<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class TestService {
  public function output() : string
  {
    return 'test';
  }
}


final class TestScalarInjection {
  public string $key;

  function __construct( string $key = 'value' )
  {
    $this->key = $key;
  }
}

final class TestServiceInjection {
  public testService $testService;

  function __construct( TestService $testService )
  {
    $this->testService = $testService;
  }

  public function service() : TestService
  {
    return $this->testService;
  }
}

abstract class TestAbstract {}

final class ServiceContainerTest extends TestCase
{
  protected ServiceContainer $service;

  protected function setUp() : void
  {
    $this->service = new ServiceContainer();
    $this->service->setDefinition('TestService', TestService::class);
  }


  public function testSetDefinition() : void
  {
    $this->service->setDefinition('test', TestService::class);

    $this->assertEquals(TestService::class, $this->service->getDefinition('test'));
  }



  public function testGetDefinition_NotFound_ThrowsException() : void
  {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage(sprintf('service %s is not defined', 'NotFound'));

    $this->service->getDefinition('NotFound');
  }

  public function testGetDefinition_Found_ReturnsTestServiceInstance() : void
  {
    $testService = $this->service->getDefinition('TestService');

    $this->assertEquals(TestService::class, $testService);
  }


  public function testGet_Unknown_ThrowsException() : void
  {
    $this->expectException(\Exception::class);

    $this->service->get('Unknown');
  }

  public function testGet_ReturnsTestServiceInstance() : void
  {
    $testService = $this->service->get('TestService');

    $this->assertInstanceOf(TestService::class, $testService);
  }


  public function testGet_InvalidClass_ThrowsException() : void
  {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage(sprintf('%s is not instantiable', TestAbstract::class));

    $this->service->setDefinition('TestAbstract', TestAbstract::class);

    $this->service->get('TestAbstract');
  }


  public function testGet_DependancyInjection_ReturnsTestServiceInjectionInstance() : void
  {
    $this->service->setDefinition('TestServiceInjection', TestServiceInjection::class);

    $testService = $this->service->get('TestServiceInjection');

    $this->assertInstanceOf(TestServiceInjection::class, $testService);
    $this->assertInstanceOf(TestService::class, $testService->service());
  }


  public function testGet_ScalarInjection_ReturnsTestScalarInjectionInstance() : void
  {
    $this->service->setDefinition('TestScalarInjection', TestScalarInjection::class);

    $testService = $this->service->get('TestScalarInjection');

    $this->assertInstanceOf(TestScalarInjection::class, $testService);
    $this->assertEquals('value', $testService->key);

  }
}
