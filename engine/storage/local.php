<?php
class Storage_Local implements Storage
{
	public function get_url($path)
	{	
		global $CONFIG;
		return "http://{$CONFIG->domain}/pg/local_store?path={$path}";
	}
	
	public function upload_file($path, $fs_path, $web_accessible = false, $headers = null)
	{
		$file_path = $this->get_file_path($path);
		$dir = dirname($file_path);
		if (!is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}
		
		file_put_contents($file_path, file_get_contents($fs_path));
	}

	public function copy_object($path, $dest_path, $web_accessible = false)
	{
		$old_path = $this->get_file_path($path);
		return $this->upload_file($dest_path, $old_path);
	}
	
	public function get_object_info($path)
	{
		$file_path = $this->get_file_path($path);
		if (is_file($file_path))
		{
			return array(
				'todo'
			);
		}
	}
	
	public function delete_object($path)
	{
		// todo
	}
	
	public function download_file($path, $fs_path)
	{
		// todo
	}

	public function get_file_path($path)
	{
		global $CONFIG;
		return "{$CONFIG->dataroot}local_store/{$path}";	
	}
}