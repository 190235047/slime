<?php
namespace SlimeFramework\Component\HTML;

/**
 * Class Form
 * @package SlimeFramework\Component\HTML
 * @author smallslime@gmail.com
 * @version 0.1
 */
class Form
{
    const TEXT = 1;
    const PASSWD = 2;
    const OPTION = 3;
    const RADIO = 4;

    public static function fill($sKey, $iType = self::TEXT, $mAttr = '', $bKeyFromGet = true)
    {
        $sValue = $bKeyFromGet ? (isset($_GET[$sKey]) ? $_GET[$sKey] : '') : $sKey;
        switch ($iType) {
            case self::TEXT:
                return $sValue ? htmlentities($_GET[$sKey]) : '';
            case self::OPTION:
                return $sValue===(string)$mAttr ? 'selected = "selected"' : '';
            default:
                trigger_error('');
                exit(1);
        }
    }

    public static function fillInputTextFromGet($sGetKey, $sDefaultValue = '') {
        return isset($_GET[$sGetKey]) ? htmlentities($_GET[$sGetKey]) : $sDefaultValue;
    }
    
    protected function _attributesToString($attributes, $formtag = FALSE)
    {
    	if (is_string($attributes) AND strlen($attributes) > 0)
    	{
    		if ($formtag == TRUE AND strpos($attributes, 'method=') === FALSE)
    		{
    			$attributes .= ' method="post"';
    		}
    
    		if ($formtag == TRUE AND strpos($attributes, 'accept-charset=') === FALSE)
    		{
    			$attributes .= ' accept-charset="'.strtolower(config_item('charset')).'"';
    		}
    
    		return ' '.$attributes;
    	}
    
    	if (is_object($attributes) AND count($attributes) > 0)
    	{
    		$attributes = (array)$attributes;
    	}
    
    	if (is_array($attributes) AND count($attributes) > 0)
    	{
    		$atts = '';
    
    		if ( ! isset($attributes['method']) AND $formtag === TRUE)
    		{
    			$atts .= ' method="post"';
    		}
    
    		if ( ! isset($attributes['accept-charset']) AND $formtag === TRUE)
    		{
    			$atts .= ' accept-charset="'.strtolower(config_item('charset')).'"';
    		}
    
    		foreach ($attributes as $key => $val)
    		{
    			$atts .= ' '.$key.'="'.$val.'"';
    		}
    
    		return $atts;
    	}
    }
    
    public static function formDropdown($name = '', $options = array(), $selected = array(), $extra = '', $title = array())
    {
    	if ( ! is_array($selected))
    	{
    		$selected = array($selected);
    	}
    
    	// If no selected state was submitted we will attempt to set it automatically
    	if (count($selected) === 0)
    	{
    		// If the form name appears in the $_POST array we have a winner!
    		if (isset($_POST[$name]))
    		{
    			$selected = array($_POST[$name]);
    		}
    	}
    
    	if ($extra != '') $extra = ' '.$extra;
    
    	$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
    
    	$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";
    
    	foreach ($options as $key => $val)
    	{
    		$key = (string) $key;
    
    		if (is_array($val) && ! empty($val))
    		{
    			$form .= '<optgroup label="'.$key.'">'."\n";
    
    			foreach ($val as $optgroup_key => $optgroup_val)
    			{
    				$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';
    
    				$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
    			}
    
    			$form .= '</optgroup>'."\n";
    		}
    		else
    		{
    			$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';
    			$altTitle = 'title="' . htmlspecialchars((string) $val) . '"';
    			if (isset($title[$key])) {
    				$altTitle = 'title="' . htmlspecialchars($title[$key]) . '"';
    			}
    			$form .= '<option ' . $altTitle . ' value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
    		}
    	}
    
    	$form .= '</select>';
    
    	return $form;
    }
    
    public static function h_($str) {
        return nl2br(htmlspecialchars($str));
    }
	
}



