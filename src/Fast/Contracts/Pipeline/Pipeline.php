<?php
namespace Fast\Contracts\Pipeline;

use Closure;
use Fast\Http\Request;

interface Pipeline
{
	/**
	 * Set the object being sent through the pipeline.
	 *
	 * @param  Request  $passable
	 * @return $this
	 */
	public function send(Request $passable): Pipeline;

	/**
	 * Set the array of pipes.
	 *
	 * @param  array  $pipes
	 * @return $this
	 */
	public function through(array $pipes): Pipeline;

	/**
	 * Set the method to call on the pipes.
	 *
	 * @param  string  $method
	 * @return $this
	 */
	public function via(string $method): Pipeline;


	/**
	 * Run the pipeline with a final handleRouting callback.
	 *
	 * @param Closure $handleRouting
	 * @return mixed
	 */
	public function then(Closure $handleRouting): mixed;
}