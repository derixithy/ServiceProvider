<?php declare(strict_types=1);


final class ServiceContainer {
  private array $definitions = [];
  private array $services    = [];


  /**
   *
   * Get service definition
   *
   * @param  string $id Service name
   * @return string     Service class
   */
  public function getDefinition( string $id ) : string
  {
    if ( ! array_key_exists($id, $this->definitions) )
      throw new Exception(sprintf('service %s is not defined', $id));

    return $this->definitions[$id];
  }


  /**
   *
   * Set sevice definition
   *
   * @param string $id    Service name
   * @param string $class Service class
   */
  public function setDefinition( string $id, string $class ) : void
  {
    $this->definitions[$id] = $class;
  }


  /**
   *
   * Get service instance
   *
   * @param  string $id [description]
   * @return object     [description]
   */
  public function get( string $id ) : object
  {
    $class = $this->getDefinition($id);

    if ( ! array_key_exists($class, $this->services) )
      $this->services[$class] = $this->resolveService($class);

    return $this->services[$class];
  }


  /**
   *
   * Resolve and instanciate given class
   *
   *
   * @param  string $class Class to resolve
   * @return object        Class instance
   */
  private function resolveService( string $class ) : object
  {

    // Create class reflector
    $reflector = new \ReflectionClass( $class );


    // Stop if class is not instantiable, e.g. abstract class
    if ( ! $reflector->isInstantiable() )
			throw new Exception("$class is not instantiable");


    // get constructor
    $constructor = $reflector->getConstructor();

    // create class if no parameters are required
    if ( is_null($constructor) )
			return new $class;


    // Validate constructor parameters
    $parameters = $constructor->getParameters();
		$dependencies = $this->resolveParameters( $parameters );

    // Return new instance
    return $reflector->newInstanceArgs( $dependencies );
  }


  /**
   *
   * Rosolves given $parameters
   *
   *
   * @param  array  $parameters Class parameters
   * @return array              Dependencies
   */
	protected function resolveParameters( array $parameters ) : array
	{
		$dependencies = array();

		foreach( $parameters as $parameter ) {
			$dependency = $parameter->getClass();

			if ( is_null($dependency) ) { // check for default values
				$dependencies[] = $this->resolveScalarParamater($parameter);
      }

			else { // get instance for class
				$dependencies[] = $this->resolveService( $dependency->name );
      }
		}

		return $dependencies;
	}


  /**
   *
   * Return default value for parameter
   *
   *
   * @param  ReflectionParameter $parameter Instance parameter
   * @return mixed                         Parameter default value
   */
	protected function resolveScalarParamater( ReflectionParameter $parameter )
	{
		// If a default value is available return the value
		if ( $parameter->isDefaultValueAvailable() )
			return $parameter->getDefaultValue();

		throw Exception::invalidArgumentException("Could not resolve default value");
	}

}
