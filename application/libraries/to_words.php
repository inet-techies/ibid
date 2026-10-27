<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * To Words
 *
 * Authentication library
 *
 * @package		Tasker
 * @author		dckap 
 * @version		1.0
 */
 
class To_words
{
	private $error = array();

	function __construct()
	{
		
	}


	function integerToWords($x)
	{

	$nwords = array(    'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 
	                     'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 
	                     'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 
	                     'nineteen', 'twenty', 30 => 'thirty', 40 => 'forty', 
	                     50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 
	                     90 => 'ninety' );

	     if(!is_numeric($x)) 
	     { 
	         $w = '#'; 
	     }else if(fmod($x, 1) != 0) 
	     { 
	         $w = '#'; 
	     }else{ 
	         if($x < 0) 
	         { 
	             $w = 'minus '; 
	             $x = -$x; 
	         }else{ 
	             $w = ''; 
	         } 
	         if($x < 21) 
	         { 
	             $w .= $nwords[$x]; 
	         }else if($x < 100) 
	         { 
	             $w .= $nwords[10 * floor($x/10)]; 
	             $r = fmod($x, 10); 
	             if($r > 0) 
	             { 
	                 $w .= '-'. $nwords[$r]; 
	             } 
	         } else if($x < 1000) 
	         { 
	             $w .= $nwords[floor($x/100)] .' hundred'; 
	             $r = fmod($x, 100); 
	             if($r > 0) 
	             { 
	                 $w .= ' and '. $this->integerToWords($r); 
	             } 
	         } else if($x < 1000000) 
	         { 
	             $w .= $this->integerToWords(floor($x/1000)) .' thousand'; 
	             $r = fmod($x, 1000); 
	             if($r > 0) 
	             { 
	                 $w .= ' '; 
	                 if($r < 100) 
	                 { 
	                     $w .= 'and'; 
	                 } 
	                 $w .= $this->integerToWords($r); 
	             } 
	         } else { 
	             $w .= $this->integerToWords(floor($x/1000000)) .' million'; 
	             $r = fmod($x, 1000000); 
	             if($r > 0) 
	             { 
	                 $w .= ' '; 
	                 if($r < 100) 
	                 { 
	                     $word .= 'and '; 
	                 } 
	                 $w .= $this->integerToWords($r); 
	             } 
	         } 
	     } 
	     return $w; 
	}

	function currencyToWords($number,$status=null)
	{
		$currencylabelsarray = array('rupees' => 'rupees', 'paise' => 'paise');
		if(!is_numeric($number)) return false;
		$nums = explode('.', $number);
		$out = $this->integerToWords($nums[0]) . ' rupees';
		if(isset($nums[1])) {
		if(isset($status) && !empty($status))
		$out .= ' and ' . $this->integerToWords($nums[1]+0) .' paise';
		else
		$out .= ' and ' . $this->integerToWords($nums[1]) .' paise';
		}
		return $out;
	}

}

/* End of file userauth.php */
/* Location: ./application/libraries/userauth.php */