<?php
function process_dir($dir, $hash_array)
{
	// Open a known directory, and proceed to read its contents
	if (is_dir($dir)) 
	{
		if ($dh = opendir($dir)) 
		{
    		while (($file = readdir($dh)) !== false) 
			{
				if ($file == "." || $file == "..")
					continue;

				$file_name = $dir . $file;

				if (is_dir($file_name))
				{
					$tmp_array = process_dir($file_name . "/", $hash_array);
					if (is_array($tmp_array))
					{
						$hash_array = array_merge($hash_array, $tmp_array);
					}
				}
				else
				{
					//$hash_array[get_relative_path($file_name)] = md5_file($file_name);
					$fc = file_get_contents($file_name);
					$fc = str_replace("\n", "", $fc);
					$fc = str_replace("\r", "", $fc);
					$hash_array[get_relative_path($file_name)] = md5($fc);
					
				}
    		}
    		closedir($dh);
		}
	}
	return $hash_array;
}

function get_relative_path($dir)
{
	$ABSPATH = dirname(__FILE__) . "/wordpress/";
	
	$pos = strpos($dir, $ABSPATH, 0);
	if ($pos !== false)
	{
		$str_pos = substr($dir, strlen($ABSPATH)+$pos);
		return $str_pos;
	}
}
$ABSPATH = dirname(__FILE__) . "/wordpress/";

$hash_array = array();
$dir_array = process_dir($ABSPATH, $hash_array);
if (count($dir_array))
{
	foreach($dir_array as $item_key => $item_val)
	{
		/* ?>$hash_array['<?php echo $item_key ?>']="<?php echo $item_val ?>;<?php echo "\r\n" ?><?php */
		echo $item_key . "=". $item_val;
		if (isset($_SERVER['REQUEST_URI']))
		{
		 	echo "<br />";
		}
		else
		{
		 	echo "\r\n";	
		}
	}
}
?>