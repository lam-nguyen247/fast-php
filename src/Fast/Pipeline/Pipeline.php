<?php
namespace Fast\Pipeline;

use Closure;
use Fast\Container;
use Fast\Http\Request;
use Fast\Contracts\Pipeline\Pipeline as IPipeline;

class Pipeline implements IPipeline {
	protected Container $container;

	protected Request $passable;

	protected array $pipes = [];

	protected string $method = 'handle';

	/**
	 * Create a new class instance.
	 *
	 * @param Container|null $container
	 */
	public function __construct(?Container $container = null) {
		$this->container = $container ?: Container::getInstance();
	}

	/**
	 * Set the object being sent through the pipeline.
	 *
	 * @param Request $passable
	 * @return $this
	 */
	function send(Request $passable): Pipeline {
		$this->passable = $passable;
		return $this;
	}

	/**
	 * Set the array of pipes.
	 *
	 * @param array $pipes
	 * @return $this
	 */
	public function through(array $pipes): Pipeline {
		$this->pipes = !empty($pipes) ? $pipes : func_get_args();
		return $this;
	}

	/**
	 * Set the method to call on the pipes.
	 *
	 * @param string $method
	 * @return $this
	 */
	public function via(string $method): Pipeline {
		$this->method = $method;
		return $this;
	}

	/**
	 * Run the pipeline with a final handleRouting callback.
	 *
	 * @param Closure $handleRouting
	 * @return mixed
	 */
	public function then(Closure $handleRouting): mixed {
		$pipeline = array_reduce(
			array_reverse($this->pipes), $this->carry(), $this->prepareHandleRouting($handleRouting)
		);
		return $pipeline($this->passable);
	}

	/**
	 * Get a Closure that represents a slice of the application onion.
	 *
	 * @return Closure
	 */
	protected function carry(): Closure {
		return function ($stack, $pipe) {
			return function ($passable) use ($stack, $pipe) {
				$pipe = Container::getInstance()->make($pipe);
				return $pipe->{$this->method}($passable, $stack);
			};
		};
	}

	/**
	 * Get the final piece of the Closure onion.
	 *
	 * @param Closure $handleRouting
	 * @return Closure
	 */
	protected function prepareHandleRouting(Closure $handleRouting): Closure {
		return function () use ($handleRouting) {
			return $handleRouting();
		};
	}
}
