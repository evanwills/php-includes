<?php

require_once(dirname(__FILE__).'/convert_chars.class.inc.php');
require_once(dirname(__FILE__).'/html_clean_elements.class.inc.php');

class html_clean extends convert_chars
{

/**
 * @var $stack array open tags as they appear in the DOM heirachy 
 */
	private $stack = array(); // stack of nested elements


/**
 * @var $current_tag object technical details about the element
 * currently being processed
 */
	private $current_tag = null;

/**
 * @var $inline boolean used to modify how white space is handled
 * when inserting or deleting it.
 */
	private $inline = false;

/**
 * @var $strip boolean are we stripping or inserting white space
 */
	private $strip = false;
/**
 * @var $stop_at_bottom boolean if grabbing part of the code,
 * $stop_at_bottom will return the code when it reaches the bottom of
 * the nested element stack
 * i.e. when all the nested elements are closed up to and inlcuding
 *      the orignal opening tag.
 *
 * NOTE: tags are automatically closed based on their relation to
 *       parent and sibling tags
 */
	private $stop_at_bottom = false;

	public $content = '';
	public $input = '';
	public $output = '';
	public $dtd = 'HTML 4.01 Transitional';

/**
 * @var $strip_elements array one or two dimensional array.
 * If one dimension only, all elements and their children listed in
 * the array will be stripped.
 * If two dimensional, the first dimension can have the following
 * keys: 'str_child' , 'keep_child' then their elements will be
 * treated accordingly.
 */
	public $strip_elements = array();

/**
 * @var $strip_attributes array multi dimensional array.
 * First dimension keyed can have the following keys:
 *	'any' attributes that have any of these values;
 *	'exclude' attrubtes that don't have these values;
 *	'only' attrubutes that have this value only;
 * Second dimension keyed to the attribute name.
 * Third dimension is the value (if any) to be compared.
 *
 * e.g.
 * $strip_attributes = array(
 *	'any' => array(
 *		'class' => array(
 *			'mso',
 *			'ping'
 *		),
 *		'border' => array(),
 *		'width' => array(),
 *	),
 *	'exclude' => array(
 *		'title' => array('this is a title')
 *	),
 *	'only' => array(
 		'style' => array('font-weight:bold;');
 *	)
 * )
 *
 * NOTE: attributes that are illegal or depricated for the specified
 *	 DTD will be deleted anyway.
 */
	public $strip_attributes = array();

	public $strip_empty = false;
	public $strip_comments = false;
	public $grab_parts = array();
	public $dtd = '';
	
	public function __construct( $dtd = 'HTML 4.01 Transitional' )
	{
		$
	}

/**
 * @method grab_part selects specific elements and their children to
 * be returned. If multiple blocks of code are found, you can choose
 * how many or which one to return.
 *
 * @param $element_name string name of element to be grabed or whole
 *	  element code.
 * @param $attr_name string name of attribute the element has to
 *	  contain to be seleected.
 * @param $attr_value string the value the attribute must contain to
 *	  be selected.
 *
 * @return string containing the block of code returned
 */
	public function grab_part( $element = '' , $attr_name = '' , $attr_value = '' )
	{
		$this->stop_at_bottom_of_stack = true;
		if(substr_compare($element,'<',0,1) == 0 && substr_compare($element,'>',-1,1) == 0 )
		{
			$this->content = preg_replace('/^.*?'.preg_quote($element).'/is','',$this->content);
		}
		else
		{
			$regex = '';
			if($attr_value != '')
			{
				$regex = preg_quote($attr_value);
			}
			else
			{
				$regex = '[^\2]*'
			}
			if($attr_name != '')
			{
				$regex = '.*?[\r\n\t ]+'.preg_quote($attr_name).'=([\'"])'.$regex.'\2[^>]*';
			}
			else
			{
				$regex = '[^>]*';
			};
			$regex = '/^.*?(<'.preg_quote($element).$regex.'>)/is';

			$this->content = preg_replace($regex,'\1',$this->content);
		};
	}


/**
 * @method grab_parts selects specific elements and their children to
 * be returned. If multiple blocks of code are found, you can choose
 * how many or which one to return.
 *
 * @param $element_name string name of element to be grabed or whole
 *	  element code.
 * @param $attr_name string name of attribute the element has to
 *	  contain to be seleected.
 * @param $attr_value string the value the attribute must contain to
 *	  be selected.
 * @param $how_many mixed integer if you want the first X code blocks
 *	  to be returned. String if you want a specific code block
 *	  returned e.g. if you want he fourth code block, $how_many
 *	  should be '4th'.
 *
 * @return array containing one or more string elements (one for each
 *	   block of code returned)
 */
	public function grab_parts( $element = '' , $attr_name = '' , $attr_value = '' , $how_many = 0 )
	{
	}


	/**
	 * @method str_el() selects which elements should be removed
	 * and whether or not to remove the element instances only or
	 * the elements and their children
	 *
	 * @param elements array list of element names to be removed.
	 */
	public function str_el( $elements = array() , $and_their_kids = true )
	{
		if(is_array($elements))
		{
		};
	}

/**
 * @method str_attr() adds values to the $strip_attributes array.
 */
	public function str_attr( $attribute , $value = '' , $with = true )
	{
	}

	public function str_com( $content = '' , $and_contents = true , $give_it_back = true )
	{
		if($content == '')
		{
			$content = $this->content;
		};
		$content = preg_replace('/<!--.*?-->/s' , '' , $content);
		if($give_it_back === true)
		{
			return $content;
		}
		else
		{
			$this->content = $content;
		};
	}

	public function strip_empty()
	{
		$this->strip_empty = true;
	}

	public function white_space( $action = 'insert' , $mode = 'normal' )
	{
	}

	public function convert_chars( )
	{
	}

	/**
	 * @method clean_up() does all the work based on parameters
	 */
	public function clean_up( $content )
	{
		$this->content = $content;
		$this->check_word_processor();
	}

	public function give_it_back()
	{
		return $this->content;
	}

	private function element_by_element()
	{	
		$regex = "/
([\\r\\n\\t\\ ]*)		#  [1]	preceeding white space if any
<
	(\/?)			#  [2]	element's closing slash if present
	([a-z]+)		#  [3]	element name
	(.*?)			#  [4]	element's attributes if any
	(\/?)			#  [5]	element's self closing slash if present
>
(.*?)				#  [6]	trailing non HTML string
(?=
	(?:
		<
			(\/?)	#  [7]	following element's closing slash if present 
			([a-z]+) # [8]	following element name
			.*?
		>
	|
		$	# end of string.
	)
)
/isx";
		$this->content = preg_replace_callback($regex,'CLEAN_ELEMENT_CALLBACK',$this->content);
	};

	private function CLEAN_ELEMENT_CALLBACK($matches)
	{
		$preceeding_white = $matches[1];
		$closing = $matches[2];
		$tag = strtolower($matches[3]);
		$attributes = strtolower($matches[4]);
		$self_close = isset($matches[5])?$matches[5]:'';
		$non_html = isset($matches[6])?$matches[6]:'';
		$follow_closing = isset($matches[7])?$matches[7]:'';
		$follow_tag = isset($matches[8])?$matches[8]:'';

		$this->current_tag = new html_element($tag,$attributes,$closing);

		if( $this->current_tag->check_valid() === true )
		{
			$this->current_tag->check_attributes();

			$this->update_stack();
		}
		elseif( $this->current_tag->keep_children === true )
		{
			return $non_html
		};
	}

	private function check_word_processor()
	{
		if(preg_match('/(?: class=(\'|")(?:Mso|Apple-style-)[^\'"]*\1|mso-[^: ]*:|<\/[owm]:[^>]*>)/isU' , $input) > 0)
		{
			
		};
	}
}

// END: html_clean class
// ==================================================================

function html_strip_ws( $content , $dtd = 'HTML 4.01 Transitional' )
{
	$through = new html_clean( $content , $dtd );
	$through->white_space( 'strip' );
	return $through->clean_up();
};

function html_insert_ws( $content , $mode = 'normal' , $dtd = 'HTML 4.01 Transitional' )
{
	$through = new html_clean( $content , $dtd );
	$through->white_space( 'insert' , $mode );
	return $through->clean_up( true);
};



