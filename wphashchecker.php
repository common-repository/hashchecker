<?php
/*
Plugin Name: Hash Checker
Plugin URI: http://www.codehooligans.com/2008/03/01/check-wordpress-core-files-for-hacks/
Description: Compare your installed WP core file against the released version. This will help identify possibly hacks applied.  
Author: Paul Menard
Version: 1.2.2
Author URI: http://www.codehooligans.com
*/

class WPHashChecker
{
	var $options_key;
	var $admin_menu_label;
	var $file_hash_array;
	
	function WPHashChecker() {
		$this->options_key		= "WPHashChecker";
		$this->admin_menu_label	= "Hash Checker";

		$this->load_config();

		$this->file_hash_array = array();

		// Add our own admin menu
		add_action('admin_menu', array(&$this,'add_nav'));

	  	if ($_REQUEST['page'] == $this->options_key)
			add_action('admin_head', array(&$this,'admin_head'));

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'activate')
			add_action('init', array(&$this,'install'));
	
	}
	function install()
	{
		
	}

	function load_config()
	{
		//$plugindir_node 				= dirname(plugin_basename(__FILE__));	
		//echo "plugindir_node=[". $plugindir_node."]<br />";
	}

	function add_nav() 
	{
    	// Add a new menu under Manage:
//    	add_management_page('WP Hash Checker', 'WP Hash Checker', 8, 
//			$this->options_key, array(&$this, 'manage_page'));

    	add_options_page($this->admin_menu_label, $this->admin_menu_label, 8, 
			$this->options_key, array(&$this, 'manage_page'));

	}
	function admin_head()
	{
	}
	
	function manage_page()
	{
			global $wp_version, $wp_db_version;
		?>
		<div class="wrap">
			<h2>Checking files for WordPress version <?php echo $wp_version ?></h2>
			<?php
			// Load the appropriate cfg file which contains the version's file list and the related file hash.
			$this->load_hash_config($wp_version);

			if (count($this->file_hash_array))
			{								
				if (isset($error_hash_array))
					unset($error_hash_array);
				$error_hash_array = array();
				
				if (isset($error_found_array))
					unset($error_found_array);
				$error_found_array = array();
				
				foreach($this->file_hash_array as $item_key => $item_val)
				{
					$core_file = ABSPATH . $item_key;
					if (is_file($core_file))
					{
						//$core_file_hash = md5_file($core_file);
						$fc = file_get_contents($core_file);
						$fc = str_replace("\n", "", $fc);
						$fc = str_replace("\r", "", $fc);
						$core_file_hash = md5($fc);
						
						if ($core_file_hash !== $item_val)
						{
							$error_hash_array[$item_key] = $item_val;
						}	
					}
					else
					{
						$error_found_array[$item_key] = $item_val;
					}
				}

				if (count($error_hash_array))
				{
					?>
					<h3>Mis-matched file:</h3>
					<p>The following files on your system do not match the original files provided for the given WordPress release. </p>
					<ul>
					<?php
					foreach($error_hash_array as $error_key => $error_val)
					{
						echo "<li>". $error_key . "</li>";
					}
					echo "</ul>";
				}
				else
				{
					?>
					<h3>Congratulations!</h3>
					<p>All check are successfull. Your WordPress core files are in tact.</p>
					<?php					
				}

				if (count($error_found_array))
				{
					?>
					<h3>Missing core files:</h3>
					<p>The following files are part of the original WordPress release but are missing from your site</p>
					<ul>
					<?php
					foreach($error_found_array as $error_key => $error_val)
					{
						echo "<li>". $error_key . "</li>";
					}
					echo "</ul>";
				}

			}
			?>
		</div>
		<?php
		
	}
/*
	function load_hash_config($version)
	{
		$hash_version_cfg 	= dirname(__FILE__) . "/wp_version_cfgs/version-". $version .".cfg";
		if (is_file($hash_version_cfg))
		{
			include ($hash_version_cfg);
			if ($hash_array)
			{
				$this->hash_array = $hash_array;
			}			
		}
	}
*/

	function load_hash_config($version)
	{
		$hash_version_cfg 	= dirname(__FILE__) . "/wp_version_cfgs/version-". $version .".cfg";
		if (is_file($hash_version_cfg))
		{
			$handle = fopen($hash_version_cfg, "r");
			if ($handle)
			{
				while (!feof($handle))
				{
					$buffer = fgets($handle, 4096);
					$buffer = trim($buffer);
					if (strlen($buffer))
					{
						list($hash_key, $hash_val) = split("=", $buffer);
						$this->file_hash_array[$hash_key] = $hash_val;
					}
			    }
			    fclose($handle);
			}
		}
	}


}
$wphashchecker = new WPHashChecker();
?>