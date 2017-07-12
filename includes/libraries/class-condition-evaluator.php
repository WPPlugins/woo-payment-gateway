<?php
/**
 * 
 * @author Payment Plugins
 *
 */
class Braintree_Gateway_Condition_Evaluator
{
	
	public $tokens = array ();
	
	private $values = array ();
	
	/**
	 *
	 * @var Braintree_Gateway_Context_Stack
	 */
	private $stack = null;
	
	private $result_stack = null;

	public function condition_operators()
	{
		return array (
				'<', 
				'>', 
				'<=', 
				'>=', 
				'AND', 
				'OR', 
				'IN', 
				'NOT_IN', 
				'EQ', 
				'NOT_EQ' 
		);
	}

	public function condition_precedence()
	{
		return array (
				'<' => 0, 
				'>' => 0, 
				'<=' => 0, 
				'>=' => 0, 
				'AND' => 1, 
				'OR' => 1, 
				'IN' => 0, 
				'NOT_IN' => 0, 
				'EQ' => 0, 
				'NOT_EQ' => 0 
		);
	}

	public function __construct()
	{
		$this->stack = new Braintree_Gateway_Context_Stack();
	}

	public function evaluate( $string, $values )
	{
		$this->values = $values;
		if ( empty( $string ) ) {
			return true;
		}
		$output = $this->convert_exp_to_postfix( $string );
		return $this->evaluate_postfix( $output );
	}

	/**
	 * Given a human readable expression, convert to postfix format.
	 * <a href="https://en.wikipedia.org/wiki/Shunting-yard_algorithm">Shunting Yard Algorithm</a>
	 *
	 * @param string $string        	
	 */
	public function convert_exp_to_postfix( $string )
	{
		$string = $this->get_values( trim( $string ) );
		$expression = '';
		// get the length of the string
		$output = array ();
		$token = '';
		
		// get all tokens from the expression.
		if ( ! preg_match_all( '/([\w.,]+)|(\[.+\])|(\()|(\))|(>=?)|(<=?)/', $string, $tokens ) ) {
			return self::trigger_error( 'invalid conditional statement.' );
		}
		$tokens = isset( $tokens [ 0 ] ) ? $tokens [ 0 ] : array ();
		// loop through each character in the string.
		foreach ( $tokens as $token ) {
			if ( in_array( $token, self::condition_operators() ) ) {
				while ( ( $o2 = $this->stack->top() ) !== null && in_array( $o2, self::condition_operators() ) && self::condition_precedence() [ $token ] >= self::condition_precedence() [ $o2 ] ) {
					$output [] = $this->stack->pop();
				}
				$this->stack->push( $token );
			} else {
				if ( $token === '(' ) {
					$this->stack->push( $token );
					$matched = true;
				} elseif ( $token === ')' ) {
					while ( ( $o = $this->stack->pop() ) !== '(' ) {
						if ( is_null( $o ) ) {
							return self::trigger_error( 'invalid paranthesis in conditional statement.' );
						} else {
							$output [] = $o;
						}
					}
				} else {
					$output [] = $token; // varable so push to output
				}
			
			}
		}
		while ( ( $o = $this->stack->pop() ) != null ) {
			$output [] = $o;
		}
		return $output;
	}

	/**
	 * Evaluate the postfix expression and return the result.
	 *
	 * @param array $output        	
	 * @return bool $result;
	 */
	public function evaluate_postfix( $tokens )
	{
		$stack = new Braintree_Gateway_Context_Stack();
		if ( ! $tokens ) {
			trigger_error( 'internal error evaluating conditional statement.' );
			return false;
		}
		foreach ( $tokens as $token ) {
			if ( in_array( $token, self::condition_operators() ) ) {
				if ( ( $op2 = $stack->pop() ) === null )
					return self::trigger_error( 'internal error' );
				if ( ( $op1 = $stack->pop() ) === null )
					return self::trigger_error( 'internal error' );
				switch( $token ) {
					case 'EQ' :
						$stack->push( $op1 == $op2 );
						break;
					case 'NOT_EQ' :
						$stack->push( $op1 != $op2 );
						break;
					case 'IN' :
						$array = explode( ',', preg_replace( '/\[|\]|\s+/', '', $op2 ) ); // remove brackets and white space
						$stack->push( in_array( $op1, $array ) );
						break;
					case 'NOT_IN' :
						$array = explode( ',', preg_replace( '/\[|\]|\s+/', '', $op2 ) ); // remove brackets and white space
						$stack->push( ! in_array( $op1, $array ) );
						break;
					case 'AND' :
						$stack->push( $op1 && $op2 );
						break;
					case 'OR' :
						$stack->push( $op1 || $op2 );
						break;
					case '<' :
						$stack->push( $op1 < $op2 );
						break;
					case '>' :
						$stack->push( $op1 > $op2 );
						break;
					case '<=' :
						$stack->push( $op1 <= $op2 );
						break;
					case '>=' :
						$stack->push( $op1 >= $op2 );
						break;
				}
			} else {
				// token is a value
				$stack->push( $token );
			}
		}
		if ( $stack->size() > 1 ) {
			self::trigger_error( 'invalid condition statement. Check your syntax.' );
			return false;
		}
		// last result left in stack is the final result.
		return $stack->pop();
	}

	public static function trigger_error( $msg )
	{
		if ( defined( 'WP_DEBUG' ) || is_admin() ) {
			trigger_error( $msg );
		}
		return false;
	}

	/**
	 * Replace all values in the conditional string with formatted values.
	 *
	 * @param string $string        	
	 * @return string $string
	 */
	public function get_values( $string )
	{
		$string = str_replace( array_keys( $this->values ), $this->values, $string );
		
		$string = str_replace( array (
				'NOT EQ', 
				'NOT IN' 
		), array (
				'NOT EQ' => 'NOT_EQ', 
				'NOT IN' => 'NOT_IN' 
		), $string );
		
		return $string;
	}
}

class Braintree_Gateway_Context_Stack
{
	private $index = - 1;
	
	private $stack = array ();

	public function push( $item )
	{
		$this->index ++;
		array_unshift( $this->stack, $item );
	}

	public function pop()
	{
		$this->index --;
		return array_shift( $this->stack );
	}

	public function top()
	{
		return ! empty( $this->stack ) ? $this->stack [ 0 ] : false;
	}

	public function is_empty()
	{
		return empty( $this->stack );
	}

	public function has_next()
	{
		$index = $this->index ++;
		return isset( $this->stack [ $index ] );
	}

	public function size()
	{
		return count( $this->stack );
	}
}